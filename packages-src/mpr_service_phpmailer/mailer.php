<?php
namespace mpr\service;

/**
 * Description of mailer
 *
 * @author GreeveX <greevex@gmail.com>
 */
class mailer
{
    
    private static $instance;
    
    /**
     *
     * @return \PHPMailer
     */
    public static function factory()
    {
        if(self::$instance == null) {
            require_once __DIR__ . '/PHPMailer/class.phpmailer.php';
            self::$instance = new \PHPMailer();
            self::$instance->IsSendmail();
            self::$instance->IsHTML();
            self::$instance->CharSet = 'utf-8';
            self::$instance->ContentType = 'text/html';
            self::$instance->AddCustomHeader('Content-Type: text/html; charset="UTF-8"');
        }
        return self::$instance;
    }
    
}