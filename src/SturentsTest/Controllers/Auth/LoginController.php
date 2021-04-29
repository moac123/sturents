<?php

namespace SturentsTest\Controllers\Auth;

use SturentsTest\Models\User;
use SturentsTest\Transformers\UserTransformer;
use Interop\Container\ContainerInterface;
use League\Fractal\Resource\Item;
use Slim\Http\Request;
use Slim\Http\Response;
use Respect\Validation\Validator as v;

class LoginController
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
     * Return token after successful login
     *
     * @param \Slim\Http\Request  $request
     * @param \Slim\Http\Response $response
     *
     * @return \Slim\Http\Response
     */
    public function login(Request $request, Response $response)
    {
        $validation = $this->validateLoginRequest($userParams = $request->getParam('user'));

        if ($validation->failed()) {
            return $response->withJson(['errors' => ['email or password' => ['is invalid']]], 422);
        }

        $user = $this->auth->attempt($userParams['email'], $userParams['password']);
        if ($user) {
            $data = $this->fractal->createData(new Item($user, new UserTransformer()))->toArray();

            return $response->withJson(['user' => $data]);
        }

        return $response->withJson(['errors' => ['email or password' => ['is invalid']]], 422);
    }

    /**
     * @param array
     *
     * @return \SturentsTest\Validation\Validator
     */
    protected function validateLoginRequest($values)
    {
        return $this->validator->validateArray($values,
            [
                'email'    => v::noWhitespace()->notEmpty(),
                'password' => v::noWhitespace()->notEmpty(),
            ]);
    }
}
