<?php

namespace Leaf\Auth;

use Leaf\Date;
use Leaf\Helpers\Authentication;
use Leaf\Helpers\Password;

/**
 * Leaf Simple Auth
 * -------------------------
 * Handle user accounts
 *
 * @author Michael Darko
 * @since 3.0
 * @version 2.0.0
 */
class User extends Session
{
    /**
     * Simple user update
     *
     * @param string $table Table to store user in
     * @param array $credentials New information for user
     * @param array $where Information to find user by
     * @param array $uniques Parameters which should be unique
     * @param array $validate Validation for parameters
     *
     * @return array user: all user info + tokens + session data
     */
    public static function update(string $table, array $credentials, array $where, array $uniques = [], array $validate = [])
    {
        $passKey = static::$settings["PASSWORD_KEY"];

        if (!isset($credentials[$passKey])) {
            static::$settings["AUTH_NO_PASS"] = true;
        }

        if (
            static::$settings["AUTH_NO_PASS"] === false &&
            static::$settings["PASSWORD_ENCODE"] !== false
        ) {
            if (is_callable(static::$settings["PASSWORD_ENCODE"])) {
                $credentials[$passKey] = call_user_func(static::$settings["PASSWORD_ENCODE"], $credentials[$passKey]);
            } else if (static::$settings["PASSWORD_ENCODE"] === "md5") {
                $credentials[$passKey] = md5($credentials[$passKey]);
            } else {
                $credentials[$passKey] = Password::hash($credentials[$passKey]);
            }
        }

        if (static::$settings["USE_TIMESTAMPS"]) {
            $credentials["updated_at"] = Date::now();
        }

        if (count($uniques) > 0) {
            foreach ($uniques as $unique) {
                if (!isset($credentials[$unique])) {
                    trigger_error("$unique not found in credentials.");
                }

                $data = static::$db->select($table)->where($unique, $credentials[$unique])->fetchAssoc();

                $wKeys = array_keys($where);
                $wValues = array_values($where);

                if (isset($data[$wKeys[0]]) && $data[$wKeys[0]] != $wValues[0]) {
                    static::$errorsArray[$unique] = "$unique already exists";
                }
            }

            if (count(static::$errorsArray) > 0) return null;
        }

        try {
            $query = static::$db->update($table)->params($credentials)->where($where)->validate($validate)->execute();
        } catch (\Throwable $th) {
            trigger_error($th->getMessage());
        }

        if (!$query) {
            static::$errorsArray = array_merge(static::$errorsArray, static::$db->errors());
            return null;
        }

        if (isset($credentials["updated_at"])) {
            unset($credentials["updated_at"]);
        }

        $user = static::$db->select($table)->where($credentials)->validate($validate)->fetchAssoc();
        if (!$user) {
            static::$errorsArray = array_merge(static::$errorsArray, static::$db->errors());
            return null;
        }

        $token = Authentication::generateSimpleToken(
            $user[static::$settings["ID_KEY"]],
            static::config("TOKEN_SECRET"),
            static::config("TOKEN_LIFETIME")
        );

        if (isset($user[static::$settings["ID_KEY"]])) {
            $userId = $user[static::$settings["ID_KEY"]];
        }

        if (static::$settings["HIDE_ID"] && isset($user[static::$settings["ID_KEY"]])) {
            unset($user[static::$settings["ID_KEY"]]);
        }

        if (static::$settings["HIDE_PASSWORD"] && (isset($user[$passKey]) || !$user[$passKey])) {
            unset($user[$passKey]);
        }

        if (!$token) {
            static::$errorsArray = array_merge(static::$errorsArray, Authentication::errors());
            return null;
        }

        if (static::config("USE_SESSION")) {
            if (isset($userId)) {
                $user[static::$settings["ID_KEY"]] = $userId;
            }

            static::save("AUTH_USER", $user);
            static::save("HAS_SESSION", true);

            if (static::config("SAVE_SESSION_JWT")) {
                static::save("AUTH_TOKEN", $token);
            }

            return $user;
        }

        $response["user"] = $user;
        $response["token"] = $token;

        return $response;
    }

    /**
     * Validate Json Web Token
     *
     * @param string $token The token validate
     * @param string $secretKey The secret key used to encode token
     */
    public static function validate($token, $secretKey = null)
    {
        $payload = Authentication::validate($token, $secretKey ?? static::config("TOKEN_SECRET"));
        if ($payload) return $payload;

        static::$errorsArray = array_merge(static::$errorsArray, Authentication::errors());

        return null;
    }

    /**
     * Validate Bearer Token
     *
     * @param string $secretKey The secret key used to encode token
     */
    public static function validateToken($secretKey = null)
    {
        $payload = Authentication::validateToken($secretKey ?? static::config("TOKEN_SECRET"));
        if ($payload) return $payload;

        static::$errorsArray = array_merge(static::$errorsArray, Authentication::errors());

        return null;
    }

    /**
     * Get Bearer token
     */
    public static function getBearerToken()
    {
        $token = Authentication::getBearerToken();
        if ($token) return $token;

        static::$errorsArray = array_merge(static::$errorsArray, Authentication::errors());

        return null;
    }

    /**
     * Return the user id encoded in token
     */
    public static function id()
    {
        if (static::config("USE_SESSION")) {
            return static::$session->get("AUTH_USER")[static::$settings["ID_KEY"]] ?? null;
        }

        $payload = static::validateToken(static::config("TOKEN_SECRET"));
        if (!$payload) return null;
        return $payload->user_id;
    }

    /**
     * Get the current user data from token
     *
     * @param string $table The table to look for user
     * @param array $hidden Fields to hide from user array
     */
    public static function info($table = "users", $hidden = [])
    {
        if (!static::id()) {
            if (static::config("USE_SESSION")) {
                return static::$session->get("AUTH_USER");
            }

            return null;
        }

        $user = static::$db->select($table)->where("id", static::id())->fetchAssoc();

        if (count($hidden) > 0) {
            foreach ($hidden as $item) {
                if (isset($user[$item]) || !$user[$item]) {
                    unset($user[$item]);
                }
            }
        }

        return $user;
    }
}
