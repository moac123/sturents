<?php

namespace SturentsTest\Controllers\User;

use Carbon\Carbon;
use SturentsTest\Transformers\UserTransformer;
use Interop\Container\ContainerInterface;
use League\Fractal\Resource\Item;
use Slim\Http\Request;
use Slim\Http\Response;
use Respect\Validation\Validator as v;

class UserController
{

    /** @var \SturentsTest\Services\Auth\Auth */
    protected $auth;
    /** @var \League\Fractal\Manager */
    protected $fractal;
    /** @var \Illuminate\Database\Capsule\Manager */
    protected $db;
    /** @var \SturentsTest\Validation\Validator */
    protected $validator;

    /**
     * UserController constructor.
     *
     * @param \Interop\Container\ContainerInterface $container
     *
     * @internal param $auth
     */
    public function __construct(ContainerInterface $container)
    {
        $this->auth = $container->get('auth');
        $this->fractal = $container->get('fractal');
        $this->validator = $container->get('validator');
        $this->db = $container->get('db');
    }

    public function show(Request $request, Response $response)
    {
        if ($user = $this->auth->requestUser($request)) {
            $data = $this->fractal->createData(new Item($user, new UserTransformer()))->toArray();

            return $response->withJson(['user' => $data]);
        }

        return $response->withJson([], 401);
    }

    public function update(Request $request, Response $response)
    {
        if ($user = $this->auth->requestUser($request)) {
            $requestParams = $request->getParam('user');

            $validation = $this->validateUpdateRequest($requestParams, $user->id);

            if ($validation->failed()) {
                return $response->withJson(['errors' => $validation->getErrors()], 422);
            }

            // Added isset to avoid adding null values to email
            $user->update([
                //'email'    => (isset($requestParams['email']) && $requestParams['email'] !== '')? $requestParams['email'] : $user->email,
                'email'    => isset($requestParams['email']) ? $requestParams['email'] : $user->email,
                'username' => isset($requestParams['username']) ? $requestParams['username'] : $user->username,
                'bio'      => isset($requestParams['bio']) ? $requestParams['bio'] : $user->bio,
                'image'    => isset($requestParams['image']) ? $requestParams['image'] : $user->image,
                'password' => isset($requestParams['password']) ? password_hash($requestParams['password'],
                    PASSWORD_DEFAULT) : $user->password,
            ]);

            $data = $this->fractal->createData(new Item($user, new UserTransformer()))->toArray();

            return $response->withJson(['user' => $data]);
        }

        return $response->withJson([], 401);
    }

    /**
     * @param array
     *
     * @return \SturentsTest\Validation\Validator
     */
    protected function validateUpdateRequest($values, $userId)
    {
        // optional accepts null or empty strings
        // not sure if is the most optimal way but i'm using v::oneof because acts like an or
        // in this case the email is accepted if is null or if a valid email
        return $this->validator->validateArray($values,
            [
                'email'    => v::oneOf(
                    v::noWhitespace()
                        ->notEmpty()
                        ->email()
                        ->existsWhenUpdate($this->db->table('users'), 'email', $userId),
                    v::nullType()
                ),
                'username' => v::optional(
                    v::noWhitespace()
                        ->notEmpty()
                        ->existsWhenUpdate($this->db->table('users'), 'username', $userId)
                ),
                'password' => v::optional(v::noWhitespace()->notEmpty()),
            ]);
    }
}
