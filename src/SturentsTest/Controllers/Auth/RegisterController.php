<?php

namespace SturentsTest\Controllers\Auth;

use SturentsTest\Models\User;
use SturentsTest\Transformers\UserTransformer;
use Interop\Container\ContainerInterface;
use League\Fractal\Resource\Item;
use Slim\Http\Request;
use Slim\Http\Response;
use Respect\Validation\Validator as v;

class RegisterController
{

    /** @var \SturentsTest\Validation\Validator */
    protected $validator;
    /** @var \Illuminate\Database\Capsule\Manager */
    protected $db;
    /** @var \League\Fractal\Manager */
    protected $fractal;
    /** @var \SturentsTest\Services\Auth\Auth */
    private $auth;

    /**
     * RegisterController constructor.
     *
     * @param \Interop\Container\ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->auth = $container->get('auth');
        $this->validator = $container->get('validator');
        $this->db = $container->get('db');
        $this->fractal = $container->get('fractal');
    }

    /**
     * Register New Users from POST Requests to /api/users
     *
     * @param \Slim\Http\Request  $request
     * @param \Slim\Http\Response $response
     *
     * @return \Slim\Http\Response
     */
    public function register(Request $request, Response $response)
    {
        $userParams = $request->getParam('user');
        $validation = $this->validateRegisterRequest($userParams);

        if ($validation->failed()) {
            return $response->withJson(['errors' => $validation->getErrors()], 422);
        }

        $user = new User($userParams);
        $user->token = $this->auth->generateToken($user);
        $user->password = password_hash($userParams['password'], PASSWORD_DEFAULT);
        $user->save();

        $resource = new Item($user, new UserTransformer());
        $user = $this->fractal->createData($resource)->toArray();

        return $response->withJson(
            [
                'user' => $user,
            ]);
    }

    /**
     * @param array
     *
     * @return \SturentsTest\Validation\Validator
     */
    protected function validateRegisterRequest($values)
    {
        return $this->validator->validateArray($values,
            [
                'email'    => v::noWhitespace()->notEmpty()->email()->existsInTable($this->db->table('users'), 'email'),
                'username' => v::noWhitespace()->notEmpty()->existsInTable($this->db->table('users'),
                    'username'),
                'password' => v::noWhitespace()->notEmpty(),
            ]);
    }
}
