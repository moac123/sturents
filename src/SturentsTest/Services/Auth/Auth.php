<?php

namespace SturentsTest\Services\Auth;

use Cassandra\Exception\UnauthorizedException;
use SturentsTest\Models\User;
use DateTime;
use Exception;
use Firebase\JWT\JWT;
use Illuminate\Database\Capsule\Manager;
use Slim\Collection;
use Slim\Http\Request;

class Auth {

	const SUBJECT_IDENTIFIER = 'username';

	/**
	 * @var \Illuminate\Database\Capsule\Manager
	 */
	private $db;
	/**
	 * @var array
	 */
	private $appConfig;

	/**
	 * Auth constructor.
	 *
	 * @param \Illuminate\Database\Capsule\Manager $db
	 * @param array|\Slim\Collection $appConfig
	 */
	public function __construct(Manager $db, Collection $appConfig){
		$this->db = $db;
		$this->appConfig = $appConfig;
	}

	/**
	 * Generate a new JWT token
	 *
	 * @param \SturentsTest\Models\User $user
	 *
	 * @return string
	 * @internal param string $subjectIdentifier The username of the subject user.
	 *
	 */
	public function generateToken(User $user){
		$now = new DateTime();
		$future = new DateTime("now +2 hours");

		$payload = [
			"iat" => $now->getTimeStamp(),
			"exp" => $future->getTimeStamp(),
			"jti" => base64_encode(random_bytes(16)),
			'iss' => $this->appConfig['app']['url'],  // Issuer
			"sub" => $user->{self::SUBJECT_IDENTIFIER},
		];

		$secret = $this->appConfig['jwt']['secret'];
		$token = JWT::encode($payload, $secret, "HS256");

		return $token;
	}

	/**
	 * Attempt to find the user based on email and verify password
	 *
	 * @param $email
	 * @param $password
	 *
	 * @return bool|\SturentsTest\Models\User
	 */
	public function attempt($email, $password){
		$user = $this->getUserByEmail($email);
        if (! $user) {
	        return false;
        }

        try {
            // wasn't cheeking what password_verify returns
            // return false if password_verify is false
            if (!password_verify($password, $user->password)) {
                return false;
            }


	        return $user;
        }
        catch (Exception $e) {
	        return false;
        }
    }

	/**
	 * @param $email
	 * @return User|null
	 */
	public function getUserByEmail($email){
		return $this->getUser('email', $email);
	}

	/**
	 * @param $name
	 * @return User|null
	 */
	public function getUserByName($name){
		return $this->getUser('username', $name);
	}

	/**
	 * @param $key
	 * @param $value
	 * @return User|null
	 */
	private function getUser($key, $value){
		$user = User::where($key, $value)->first();

		if (!$user){
			return null;
		}

		$user->token = $this->generateToken($user);

		return $user;
	}

	/**
	 * Retrieve a user by the JWT token from the request
	 *
	 * @param \Slim\Http\Request $request
	 *
	 * @return User|null
	 */
	public function requestUser(Request $request){
		// Not sure if is the right approach but to add an extra layer of security everytime the user logs in i'm updating
        // the token to then check in the requests if the session is valid
		if ($token = $request->getAttribute('token')){
            $secret = $this->appConfig['jwt']['secret'];
            $currentToken = JWT::encode($token, $secret, "HS256");
            return User::where(static::SUBJECT_IDENTIFIER, '=', $token->sub)->where('token', '=', $currentToken)->first();
		}
	}

}
