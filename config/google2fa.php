<?php

return [

    /*
     * Is Google2FA enabled?
     */
    'enabled' => true,

    /*
     * The session var that holds the request status.
     */
    'session_var' => 'google2fa',

    /*
     * The master key used to encrypt your secrets.
     */
    'secret' => env('GOOGLE2FA_SECRET'),

    /*
     * The window of time that a TOTP is valid, in seconds.
     */
    'window' => 1,

    /*
     * The length of the one-time password.
     */
    'otp_length' => 6,

    /*
     * The algorithm to use for hashing.
     */
    'algorithm' => 'sha1',

    /*
     * The view that will be used to ask the user for their one-time password.
     */
    'view' => 'google2fa.index',

    /*
     * The name of the property that holds the user's Google2FA secret.
     */
    'secret_column' => 'google2fa_secret',

    /*
     * The name of the session var that holds the user's OTP.
     */
    'otp_session_var' => 'google2fa_otp',

    /*
     * The name of the session var that holds the user's OTP timestamp.
     */
    'otp_timestamp_session_var' => 'google2fa_otp_timestamp',

    /*
     * The name of the session var that holds the user's OTP attempts.
     */
    'otp_attempts_session_var' => 'google2fa_otp_attempts',

    /*
     * The number of OTP attempts the user can make before being locked out.
     */
    'max_otp_attempts' => 5,

    /*
     * The number of minutes the user will be locked out for after exceeding the max OTP attempts.
     */
    'otp_lockout_duration' => 5,

];
