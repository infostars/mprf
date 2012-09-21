<?php
namespace sds\twitter;

use \mpr\debug\log;
use \mpr\config;

/**
 * A class that makes it easy to connect to and consume the Twitter stream via the Streaming API.
 *
 * Note: This is beta software - Please read the following carefully before using:
 *  - http://code.google.com/p/streamClient/wiki/Introduction
 *  - http://dev.twitter.com/pages/streaming_api
 * @author  Fenn Bailey <fenn.bailey@gmail.com>
 * @author  GreeveX <greevex@gmail.com>
 * @version 2.1
 */
class streamClient
{
    /**
     * Class constants
     */
    const URL_BASE = 'https://stream.twitter.com/1/statuses/';
    const FORMAT_JSON = 'json';
    const FORMAT_XML = 'xml';
    const METHOD_FILTER = 'filter';
    const METHOD_SAMPLE = 'sample';
    const METHOD_RETWEET = 'retweet';
    const METHOD_FIREHOSE = 'firehose';
    const EARTH_RADIUS_KM = 6371;

    protected $out_address = null;

    public function setOutputAddress($out_address)
    {
        $this->out_address = $out_address;
    }

    /**
     * Member Attribs
     */
    protected $username;
    protected $password;
    protected $method;
    protected $format;
    protected $count; //Can be -150,000 to 150,000. @see http://dev.twitter.com/pages/streaming_api_methods#count
    protected $followIds;
    protected $trackWords;
    protected $locationBoxes;
    protected $conn;
    protected $fdrPool;
    protected $buff;
    // State vars
    protected $filterChanged;
    protected $reconnect;

    protected $max_bytes_read;

    /**
     * The number of tweets received per second in previous minute; calculated fresh
     * just before each call to statusUpdate()
     * I.e. if fewer than 30 tweets in last minute then this will be zero; if 30 to 90 then it
     * will be 1, if 90 to 150 then 2, etc.
     *
     * @var integer
     */
    protected $statusRate;
    protected $lastErrorNo;
    protected $lastErrorMsg;

    /**
     * Number of tweets received.
     *
     * Note: by default this is the sum for last 60 seconds, and is therefore
     * reset every 60 seconds.
     * To change this behaviour write a custom statusUpdate() function.
     *
     * @var integer
     */
    protected $statusCount = 0;

    /**
     * The number of calls to $this->checkFilterPredicates().
     *
     * By default it is called every 5 seconds, so if doing statusUpdates every
     * 60 seconds and then resetting it, this will usually be 12.
     *
     * @var integer
     */
    protected $filterCheckCount = 0;

    /**
     * Total number of seconds (fractional) spent in the enqueueStatus() calls (i.e. the customized
     * function that handles each received tweet).
     *
     * @var float
     */
    protected $enqueueSpent = 0;

    /**
     * Total number of seconds (fractional) spent in the checkFilterPredicates() calls
     *
     * @var float
     */
    protected $filterCheckSpent = 0;

    /**
     * Number of seconds since the last tweet arrived (or the keep-alive newline)
     *
     * @var integer
     */
    protected $idlePeriod = 0;

    /**
     * The maximum value $this->idlePeriod has reached.
     *
     * @var integer
     */
    protected $maxIdlePeriod = 0;

    /**
     * Time spent on each call to enqueueStatus() (i.e. average time spent, in milliseconds,
     * spent processing received tweet).
     *
     * Simply: enqueueSpent divided by statusCount
     * Note: by default, calculated fresh for past 60 seconds, every 60 seconds.
     *
     * @var float
     */
    protected $enqueueTimeMS = 0;

    /**
     * Like $enqueueTimeMS but for the checkFilterPredicates() function.
     * @var float
     */
    protected $filterCheckTimeMS = 0;

    /**
     * Seconds since the last call to statusUpdate()
     *
     * Reset to zero after each call to statusUpdate()
     * Highest value it should ever reach is $this->avgPeriod
     *
     * @var integer
     */
    protected $avgElapsed = 0;
    // Config type vars - override in subclass if desired
    protected $connectFailuresMax = 20;
    protected $connectTimeout = 5;
    protected $readTimeout = 5;
    protected $idleReconnectTimeout = 90;
    protected $avgPeriod = 60;
    protected $status_length_base = 10;
    protected $userAgent = 'streamClient/0.9';
    protected $filterCheckMin = 5;
    protected $filterUpdMin = 120;
    protected $tcpBackoff = 1;
    protected $tcpBackoffMax = 16;
    protected $httpBackoff = 10;
    protected $httpBackoffMax = 240;

    /**
     * Create a new streamClient object attached to the appropriate twitter stream method.
     * Methods are: METHOD_FIREHOSE, METHOD_RETWEET, METHOD_SAMPLE, METHOD_FILTER
     * Formats are: FORMAT_JSON, FORMAT_XML
     * @see streamClient::METHOD_SAMPLE
     * @see streamClient::FORMAT_JSON
     *
     * @param string $username Any twitter username
     * @param string $password Any twitter password
     * @param string $method
     * @param string $format
     */
    public function __construct($username, $password, $method = streamClient::METHOD_SAMPLE, $format = self::FORMAT_JSON) {
        $this->username = $username;
        $this->password = $password;
        $this->method = $method;
        $this->format = $format;
        $this->method_to_call = array($this, 'enqueueStatus');
    }

    /**
     * Returns public statuses from or in reply to a set of users. Mentions ("Hello @user!") and implicit replies
     * ("@user Hello!" created without pressing the reply button) are not matched. It is up to you to find the integer
     * IDs of each twitter user.
     * Applies to: METHOD_FILTER
     *
     * @param array $userIds Array of Twitter integer userIDs
     */
    public function setFollow($userIds) {
        $userIds = ($userIds === NULL) ? array() : $userIds;
        sort($userIds); // Non-optimal but necessary
        if ($this->followIds != $userIds) {
            $this->filterChanged = TRUE;
        }
        $this->followIds = $userIds;
    }

    /**
     * Returns an array of followed Twitter userIds (integers)
     *
     * @return array
     */
    public function getFollow() {
        return $this->followIds;
    }

    /**
     * Specifies keywords to track. Track keywords are case-insensitive logical ORs. Terms are exact-matched, ignoring
     * punctuation. Phrases, keywords with spaces, are not supported. Queries are subject to Track Limitations.
     * Applies to: METHOD_FILTER
     *
     * See: http://apiwiki.twitter.com/Streaming-API-Documentation#TrackLimiting
     *
     * @param array $trackWords
     */
    public function setTrack($trackWords) {
        $trackWords = ($trackWords === NULL) ? array() : $trackWords;
        sort($trackWords); // Non-optimal, but necessary
        if ($this->trackWords != $trackWords) {
            $this->filterChanged = TRUE;
        }
        $this->trackWords = $trackWords;
    }

    /**
     * Returns an array of keywords being tracked
     *
     * @return array
     */
    public function getTrack() {
        return $this->trackWords;
    }

    /**
     * Specifies a set of bounding boxes to track as an array of 4 element lon/lat pairs denoting <south-west point>,
     * <north-east point>. Only tweets that are both created using the Geotagging API and are placed from within a tracked
     * bounding box will be included in the stream. The user's location field is not used to filter tweets. Bounding boxes
     * are logical ORs and must be less than or equal to 1 degree per side. A locations parameter may be combined with
     * track parameters, but note that all terms are logically ORd.
     *
     * NOTE: The argument order is Longitude/Latitude (to match the Twitter API and GeoJSON specifications).
     *
     * Applies to: METHOD_FILTER
     *
     * See: http://apiwiki.twitter.com/Streaming-API-Documentation#locations
     *
     * Eg:
     *  setLocations(array(
     *      array(-122.75, 36.8, -121.75, 37.8), // San Francisco
     *      array(-74, 40, -73, 41),             // New York
     *  ));
     *
     * @param array $boundingBoxes
     */
    public function setLocations($boundingBoxes) {
        $boundingBoxes = ($boundingBoxes === NULL) ? array() : $boundingBoxes;
        // Flatten to single dimensional array
        $locationBoxes = array();
        foreach ($boundingBoxes as $boundingBox) {
            // Sanity check
            if (count($boundingBox) != 4) {
                // Invalid - Not much we can do here but log error
                log::put('Invalid location bounding box: [' . implode(', ', $boundingBox) . ']', config::getPackageName(__CLASS__));
                return FALSE;
            }
            // Append this lat/lon pairs to flattened array
            $locationBoxes = array_merge($locationBoxes, $boundingBox);
        }
        // If it's changed, make note
        if ($this->locationBoxes != $locationBoxes) {
            $this->filterChanged = TRUE;
        }
        // Set flattened value
        $this->locationBoxes = $locationBoxes;
    }

    /**
     * Returns an array of 4 element arrays that denote the monitored location bounding boxes for tweets using the
     * Geotagging API.
     *
     * @see setLocations()
     * @return array
     */
    public function getLocations() {
        if ($this->locationBoxes == NULL) {
            return NULL;
        }
        $locationBoxes = $this->locationBoxes; // Copy array
        $ret = array();
        while (count($locationBoxes) >= 4) {
            $ret[] = array_splice($locationBoxes, 0, 4); // Append to ret array in blocks of 4
        }
        return $ret;
    }

    /**
     * Convenience method that sets location bounding boxes by an array of lon/lat/radius sets, rather than manually
     * specified bounding boxes. Each array element should contain 3 element subarray containing a latitude, longitude and
     * radius. Radius is specified in kilometers and is approximate (as boxes are square).
     *
     * NOTE: The argument order is Longitude/Latitude (to match the Twitter API and GeoJSON specifications).
     *
     * Eg:
     *  setLocationsByCircle(array(
     *      array(144.9631, -37.8142, 30), // Melbourne, 3km radius
     *      array(-0.1262, 51.5001, 25),   // London 10km radius
     *  ));
     *
     *
     * @see setLocations()
     * @param array
     */
    public function setLocationsByCircle($locations) {
        $boundingBoxes = array();
        foreach ($locations as $locTriplet) {
            // Sanity check
            if (count($locTriplet) != 3) {
                // Invalid - Not much we can do here but log error
                log::put('Invalid location triplet for ' . __METHOD__ . ': [' . implode(', ', $locTriplet) . ']', config::getPackageName(__CLASS__));
                return FALSE;
            }
            list($lon, $lat, $radius) = $locTriplet;

            // Calc bounding boxes
            $maxLat = round($lat + rad2deg($radius / self::EARTH_RADIUS_KM), 2);
            $minLat = round($lat - rad2deg($radius / self::EARTH_RADIUS_KM), 2);
            // Compensate for degrees longitude getting smaller with increasing latitude
            $maxLon = round($lon + rad2deg($radius / self::EARTH_RADIUS_KM / cos(deg2rad($lat))), 2);
            $minLon = round($lon - rad2deg($radius / self::EARTH_RADIUS_KM / cos(deg2rad($lat))), 2);
            // Add to bounding box array
            $boundingBoxes[] = array($minLon, $minLat, $maxLon, $maxLat);
        }
        // Set by bounding boxes
        $this->setLocations($boundingBoxes);
    }

    /**
     * Sets the number of previous statuses to stream before transitioning to the live stream. Applies only to firehose
     * and filter + track methods. This is generally used internally and should not be needed by client applications.
     * Applies to: METHOD_FILTER, METHOD_FIREHOSE
     *
     * @param integer $count
     */
    public function setCount($count) {
        $this->count = $count;
    }

    /**
     * Connects to the stream API and consumes the stream. Each status update in the stream will cause a call to the
     * handleStatus() method.
     *
     * @param bool $reconnect
     */
    public function consume($reconnect = TRUE) {
        // Persist connection?
        $this->reconnect = $reconnect;
        log::put("Consuming", config::getPackageName(__CLASS__));
        // Loop indefinitely based on reconnect
        do {
            try {
                // (Re)connect
                log::put("(Re)connecting...", config::getPackageName(__CLASS__));
                $this->reconnect();
                log::put("Connected!", config::getPackageName(__CLASS__));
                // Init state
                $lastAverage = $lastFilterCheck = $lastFilterUpd = $lastStreamActivity = time();
                $fdw = $fde = NULL; // Placeholder write/error file descriptors for stream_select
                // We use a blocking-select with timeout, to allow us to continue processing on idle streams
                while ($this->conn !== NULL && !feof($this->conn) && ($numChanged = stream_select($this->fdrPool, $fdw, $fde, $this->readTimeout)) !== FALSE) {
                    /* Unfortunately, we need to do a safety check for dead twitter streams - This seems to be able to happen where
                     * you end up with a valid connection, but NO tweets coming along the wire (or keep alives). The below guards
                     * against this.
                     */
                    if ((time() - $lastStreamActivity) > $this->idleReconnectTimeout) {
                        log::put("Idle timeout: No stream activity for > {$this->idleReconnectTimeout} seconds. \nReconnecting.", config::getPackageName(__CLASS__));
                        $this->reconnect();
                        $lastStreamActivity = time();
                        continue;
                    }
                    // Process stream/buffer
                    $this->fdrPool = array($this->conn); // Must reassign for stream_select()
                    $this->buff = trim(stream_get_line($this->conn, 1000000, "\n")); // ~7000 std so x10 ;)
                    try {
                        call_user_func($this->method_to_call, $this->buff);
                    } catch(\Exception $e) {
                        log::put("User method calling failed with message: {$e->getMessage()} in {$e->getFile()}:{$e->getLine()}", config::getPackageName(__CLASS__));
                    }
                    if(!empty($this->buff)) {
                        $lastStreamActivity = time();
                    }
                } // End while-stream-activity
                // Some sort of socket error has occured
            } catch(\Exception $ex) {
                log::put("streamClient connection error occured: {$ex->getMessage()}", config::getPackageName(__CLASS__));
                $waitbefore = 5;
                $rc_output = "reconnecting after {$waitbefore} seconds";
                log::put($rc_output, config::getPackageName(__CLASS__));
                sleep($waitbefore);
            }

            // Reconnect
        } while ($this->reconnect);

        // Exit
        log::put('Exiting.', config::getPackageName(__CLASS__));
    }

    private $method_to_call;

    public function setCallbackMethod($callback)
    {
        $this->method_to_call = $callback;
    }
    /**
     * Returns the last error message (TCP or HTTP) that occured with the streaming API or client. State is cleared upon
     * successful reconnect
     * @return string
     */
    public function getLastErrorMsg() {
        return $this->lastErrorMsg;
    }

    /**
     * Returns the last error number that occured with the streaming API or client. Numbers correspond to either the
     * fsockopen() error states (in the case of TCP errors) or HTTP error codes from Twitter (in the case of HTTP errors).
     *
     * State is cleared upon successful reconnect.
     *
     * @return string
     */
    public function getLastErrorNo() {
        return $this->lastErrorNo;
    }

    protected function convertUsers2Ids($users)
    {
        $url = "http://api.twitter.com/1/users/lookup.json?screen_name=";
        $usernames = [];
        $userIds = [];
        $total_users = count($users);
        for($block = 0; $block < $total_users; $block += 100) {
            echo "Requesting user ids...[{$block}/{$total_users}]...\n";
            $usernames = urlencode(implode(',', array_slice($users, $block, 100)));
            $requestedUserInfo = json_decode(file_get_contents($url.$usernames), 1);
            foreach($requestedUserInfo as $requestedUser) {
                $userIds[] = $requestedUser['id'];
            }
        }
        echo "Users parsed " . count($userIds) . PHP_EOL;
        var_dump($userIds);
        return $userIds;
    }

    /**
     * Connects to the stream URL using the configured method.
     * 
     * @throws streamClientConnectLimitExceeded
     * @throws streamClientNetworkException
     */
    protected function connect() {
        log::put("Connecting to twitter streaming api...", config::getPackageName(__CLASS__));
        // Init state
        $connectFailures = 0;
        $tcpRetry = $this->tcpBackoff / 2;
        $httpRetry = $this->httpBackoff / 2;

        // Keep trying until connected (or max connect failures exceeded)
        do {
            log::put("Constructing connection...", config::getPackageName(__CLASS__));
            // Check filter predicates for every connect (for filter method)
            if ($this->method == self::METHOD_FILTER) {
                $this->checkFilterPredicates();
            }

            // Construct URL/HTTP bits
            $url = self::URL_BASE . $this->method . '.' . $this->format;
            $urlParts = parse_url($url);

            // Setup params appropriately
            //$requestParams = array('delimited' => 'length');
            $requestParams = array();

            // Filter takes additional parameters
            if (count($this->trackWords)) {
                $requestParams['track'] = implode(',', $this->trackWords);
            }
            if (count($this->followIds)) {
                $requestParams['follow'] = implode(',', $this->convertUsers2Ids($this->followIds));
            }
            if (count($this->locationBoxes)) {
                $requestParams['locations'] = implode(',', $this->locationBoxes);
            }
            if ($this->count <> 0) {
                $requestParams['count'] = $this->count;
            }

            // Debugging is useful
            log::put("Connecting to twitter stream: {$url} with params: " .
                        str_replace("\n", '', var_export($requestParams, 1)), config::getPackageName(__CLASS__));

            /**
             * Open socket connection to make POST request. It'd be nice to use stream_context_create with the native
             * HTTP transport but it hides/abstracts too many required bits (like HTTP error responses).
             */
            $errNo = $errStr = NULL;
            $scheme = ($urlParts['scheme'] == 'https') ? 'ssl://' : 'tcp://';
            $port = ($urlParts['scheme'] == 'https') ? 443 : 80;

            /**
             * We must perform manual host resolution here as Twitter's IP regularly rotates (ie: DNS TTL of 60 seconds) and
             * PHP appears to cache it the result if in a long running process (as per streamClient).
             */
            $streamIPs = gethostbynamel($urlParts['host']);
            if (empty($streamIPs)) {
                throw new streamClientNetworkException("Unable to resolve hostname: '" . $urlParts['host'] . '"');
            }

            // Choose one randomly (if more than one)
            log::put("Resolved host {$urlParts['host']} to " . implode(', ', $streamIPs), config::getPackageName(__CLASS__));
            $streamIP = $streamIPs[rand(0, (count($streamIPs) - 1))];
            log::put('Connecting to ' . $streamIP, config::getPackageName(__CLASS__));

            $ips = trim(shell_exec("/sbin/ip a | grep \"inet \" | awk '{print \$2}' | cut -d \"/\" -f 1 | grep -v '127.0.0'"));
            $ips = explode("\n", $ips);
            $context_options = array();
            if(in_array($this->out_address, $ips)) {
                $context_options['socket']['bindto'] = "{$this->out_address}:0";
            }
            $context = stream_context_create($context_options);
            $this->conn = stream_socket_client("{$scheme}{$streamIP}:{$port}", $errNo, $errStr, $this->connectTimeout, STREAM_CLIENT_CONNECT, $context);

            // No go - handle errors/backoff
            if (!$this->conn || !is_resource($this->conn)) {
                $this->lastErrorMsg = $errStr;
                $this->lastErrorNo = $errNo;
                $connectFailures++;
                if ($connectFailures > $this->connectFailuresMax) {
                    $msg = 'TCP failure limit exceeded with ' . $connectFailures . ' failures. Last error: ' . $errStr;
                    log::put($msg, 'error');
                    throw new streamClientConnectLimitExceeded($msg, $errNo); // Throw an exception for other code to handle
                }
                // Increase retry/backoff up to max
                $tcpRetry = ($tcpRetry < $this->tcpBackoffMax) ? $tcpRetry * 2 : $this->tcpBackoffMax;
                log::put('TCP failure ' . $connectFailures . ' of ' . $this->connectFailuresMax . ' connecting to stream: ' .
                        $errStr . ' (' . $errNo . '). Sleeping for ' . $tcpRetry . ' seconds.', config::getPackageName(__CLASS__));
                sleep($tcpRetry);
                continue;
            }

            // TCP connect OK, clear last error (if present)
            log::put('Connection established to ' . $streamIP, config::getPackageName(__CLASS__));
            $this->lastErrorMsg = NULL;
            $this->lastErrorNo = NULL;

            // If we have a socket connection, we can attempt a HTTP request - Ensure blocking read for the moment
            stream_set_blocking($this->conn, 1);

            // Encode request data
            $postData = http_build_query($requestParams, NULL, '&');
            $postData = str_replace('+', '%20', $postData);
            $authCredentials = $this->getAuthorizationHeader();

            log::put("Sending headers...", config::getPackageName(__CLASS__));
            log::put("Auth: {$authCredentials}", config::getPackageName(__CLASS__));
            // Do it
            fwrite($this->conn, "POST " . $urlParts['path'] . " HTTP/1.0\r\n");
            fwrite($this->conn, "Host: " . $urlParts['host'] . ':' . $port . "\r\n");
            fwrite($this->conn, "Content-type: application/x-www-form-urlencoded\r\n");
            fwrite($this->conn, "Content-length: " . strlen($postData) . "\r\n");
            fwrite($this->conn, "Accept: */*\r\n");
            fwrite($this->conn, 'Authorization: ' . $authCredentials . "\r\n");
            fwrite($this->conn, 'User-Agent: ' . $this->userAgent . "\r\n");
            fwrite($this->conn, "\r\n");
            fwrite($this->conn, $postData . "\r\n");
            fwrite($this->conn, "\r\n");

            log::put("POST " . $urlParts['path'] . " HTTP/1.0", config::getPackageName(__CLASS__));
            log::put("Host: " . $urlParts['host'] . ':' . $port, config::getPackageName(__CLASS__));
            log::put("Content-type: application/x-www-form-urlencoded", config::getPackageName(__CLASS__));
            log::put("Content-length: " . strlen($postData), config::getPackageName(__CLASS__));
            log::put("Accept: */*", config::getPackageName(__CLASS__));
            log::put('Authorization: ' . $authCredentials, config::getPackageName(__CLASS__));
            log::put('User-Agent: ' . $this->userAgent, config::getPackageName(__CLASS__));
            log::put("Reading response...", config::getPackageName(__CLASS__));
            // First line is response
            try {
                $line = trim(fgets($this->conn, 4096));
                list($httpVer, $httpCode, $httpMessage) = preg_split('/\s+/', $line, 3);
            } catch(\Exception $e) {
                $httpVer = 'HTTP/1.0';
                $httpCode = 200;
                $httpMessage = 'Twitter is empty!?';
            }

            // Response buffers
            $respHeaders = $respBody = '';

            // Consume each header response line until we get to body
            while ($hLine = trim(fgets($this->conn, 4096))) {
                $respHeaders .= $hLine;
            }
            log::put("Response Headers:", config::getPackageName(__CLASS__));
            log::put($respHeaders, config::getPackageName(__CLASS__));

            // If we got a non-200 response, we need to backoff and retry
            if ($httpCode != 200) {
                $connectFailures++;

                // Twitter will disconnect on error, but we want to consume the rest of the response body (which is useful)
                $respBody = trim(stream_get_line($this->conn, 10000000, "\n"));

                // Construct error
                $errStr = "HTTP ERROR $httpCode: $respBody";

                log::put(">>> ERROR-CODE:{$httpCode}", config::getPackageName(__CLASS__));
                log::put(">>> ERROR-MSG :{$errStr}", config::getPackageName(__CLASS__));

                // Set last error state
                $this->lastErrorMsg = $errStr;
                $this->lastErrorNo = $httpCode;

                // Have we exceeded maximum failures?
                if ($connectFailures > $this->connectFailuresMax) {
                    $msg = 'Connection failure limit exceeded with ' . $connectFailures . ' failures. Last error: ' . $errStr;
                    throw new streamClientConnectLimitExceeded($msg, $httpCode); // We eventually throw an exception for other code to handle
                }
                // Increase retry/backoff up to max
                $httpRetry = ($httpRetry < $this->httpBackoffMax) ? $httpRetry * 2 : $this->httpBackoffMax;
                sleep($httpRetry);
                continue;
            } // End if not http 200
            stream_set_blocking($this->conn, 0);
        } while (!is_resource($this->conn) || $httpCode != 200);

        // Connected OK, reset connect failures
        $connectFailures = 0;
        $this->lastErrorMsg = NULL;
        $this->lastErrorNo = NULL;

        // Switch to non-blocking to consume the stream (important)
        stream_set_blocking($this->conn, 0);

        // Connect always causes the filterChanged status to be cleared
        $this->filterChanged = FALSE;

        // Flush stream buffer & (re)assign fdrPool (for reconnect)
        $this->fdrPool = array($this->conn);
        $this->buff = '';
    }

    protected function getAuthorizationHeader() {
        $authCredentials = base64_encode($this->username . ':' . $this->password);
        return "Basic: " . $authCredentials;
    }

    /**
     * Method called as frequently as practical (every 5+ seconds) that is responsible for checking if filter predicates
     * (ie: track words or follow IDs) have changed. If they have, they should be set using the setTrack() and setFollow()
     * methods respectively within the overridden implementation.
     *
     * Note that even if predicates are changed every 5 seconds, an actual reconnect will not happen more frequently than
     * every 2 minutes (as per Twitter Streaming API documentation).
     *
     * Note also that this method is called upon every connect attempt, so if your predicates are causing connection
     * errors, they should be checked here and corrected.
     *
     * This should be implemented/overridden in any subclass implementing the FILTER method.
     *
     * @see setTrack()
     * @see setFollow()
     * @see streamClient::METHOD_FILTER
     */
    protected function checkFilterPredicates() {
        // Override in subclass
    }

    /**
     * Basic log function that outputs logging to the standard error_log() handler. This should generally be overridden
     * to suit the application environment.
     *
     * @see error_log()
     * @param string $messages
     * @param String $level 'error', 'info', 'notice'. Defaults to 'notice', so you should set this
     *     parameter on the more important error messages.
     *     'info' is used for problems that the class should be able to recover from automatically.
     *     'error' is for exceptional conditions that may need human intervention. (For instance, emailing
     *          them to a system administrator may make sense.)
     */
    protected function log($message, $level = 'notice') {
        log::put($message, config::getPackageName(__CLASS__));
    }

    /**
     * Performs forcible disconnect from stream (if connected) and cleanup.
     */
    protected function disconnect() {
        if (is_resource($this->conn)) {
            log::put('Closing streamClient connection.', config::getPackageName(__CLASS__));
            fclose($this->conn);
        }
        $this->conn = NULL;
        $this->reconnect = FALSE;
    }

    /**
     * Reconnects as quickly as possible. Should be called whenever a reconnect is required rather that connect/disconnect
     * to preserve streams reconnect state
     */
    private function reconnect() {
        $reconnect = $this->reconnect;
        log::put("disconnecting...", config::getPackageName(__CLASS__));
        $this->disconnect(); // Implicitly sets reconnect to FALSE
        log::put("set reconnect status", config::getPackageName(__CLASS__));
        $this->reconnect = $reconnect; // Restore state to prev
        log::put("connecting", config::getPackageName(__CLASS__));
        $this->connect();
    }

    /**
     * This is the one and only method that must be implemented additionally. As per the streaming API documentation,
     * statuses should NOT be processed within the same process that is performing collection
     *
     * @param string $status
     */
    public function enqueueStatus($status)
    {
        var_dump($status);
    }

    /**
     * Reports a periodic heartbeat. Keep execution time minimal.
     *
     * @return NULL
     */
    public function heartbeat() {

    }

}

// End of class

class streamClientException extends \Exception {

}

class streamClientNetworkException extends streamClientException {

}

class streamClientConnectLimitExceeded extends streamClientException {

}
