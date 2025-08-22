<?php

namespace Shared\Utils;

/**
 * Validation utility class for the Healthcare Management System
 */
class ValidationUtils
{
    /**
     * Validate email format
     *
     * @param string $email
     * @return bool
     */
    public static function validateEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validate phone number format
     *
     * @param string $phone
     * @return bool
     */
    public static function validatePhone($phone)
    {
        // Remove any non-digit characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Check if the phone number is between 10 and 15 digits
        return strlen($phone) >= 10 && strlen($phone) <= 15;
    }

    /**
     * Validate password strength
     *
     * @param string $password
     * @return bool
     */
    public static function validatePassword($password)
    {
        // Check if password is at least 8 characters long
        if (strlen($password) < 8) {
            return false;
        }
        
        // Check if password contains at least one uppercase letter
        if (!preg_match('/[A-Z]/', $password)) {
            return false;
        }
        
        // Check if password contains at least one lowercase letter
        if (!preg_match('/[a-z]/', $password)) {
            return false;
        }
        
        // Check if password contains at least one digit
        if (!preg_match('/[0-9]/', $password)) {
            return false;
        }
        
        // Check if password contains at least one special character
        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            return false;
        }
        
        return true;
    }

    /**
     * Validate date format
     *
     * @param string $date
     * @param string $format
     * @return bool
     */
    public static function validateDate($date, $format = 'Y-m-d')
    {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }

    /**
     * Validate time format
     *
     * @param string $time
     * @param string $format
     * @return bool
     */
    public static function validateTime($time, $format = 'H:i')
    {
        $d = DateTime::createFromFormat($format, $time);
        return $d && $d->format($format) == $time;
    }
}