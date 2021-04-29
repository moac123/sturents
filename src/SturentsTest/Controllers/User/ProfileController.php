<?php

namespace SturentsTest\Controllers\User;

use SturentsTest\Models\User;
use SturentsTest\Transformers\ProfileTransformer;
use SturentsTest\Transformers\UserTransformer;
use Interop\Container\ContainerInterface;
use League\Fractal\Resource\Item;
use Slim\Http\Request;
use Slim\Http\Response;

class ProfileController
{

    /** @var \SturentsTest\Services\Auth\Auth */
    protected $auth;
    /** @var \League\Fractal\Manager */
    protected $fractal;

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
    }

    public function show(Request $request, Response $response, array $args)
    {
        $user = $this->auth->getUserByName($args['username']);
        $profile = $user->toArray();

        $requestUser = $this->auth->requestUser($request);
        $followingStatus = $requestUser ? $requestUser->isFollowing($user->id) : false;
        $profile['following'] = $followingStatus;

        return $response->withJson(
            [
                'profile' => $profile,
            ]
        );
    }

    public function follow(Request $request, Response $response, array $args)
    {
        $requestUser = $this->auth->requestUser($request);
        $user = User::query()->where('username', $args['username'])->firstOrFail();

        $requestUser->follow($user->id);

        return $response->withJson(
            [
                'profile' => [
                    'username'  => $user->username,
                    'bio'       => $user->bio,
                    'image'     => $user->image,
                    'following' => $user->isFollowedBy($requestUser),
                ],
            ]
        );
    }

    public function unfollow(Request $request, Response $response, array $args)
    {
        $requestUser = $this->auth->requestUser($request);
        $user = User::query()->where('username', $args['username'])->firstOrFail();

        $requestUser->un_follow($user->id);

        return $response->withJson(
            [
                'profile' => [
                    'username'  => $user->username,
                    'bio'       => $user->bio,
                    'image'     => $user->image,
                    'following' => $requestUser->isFollowing($user->id),
                ],
            ]
        );
    }

}
