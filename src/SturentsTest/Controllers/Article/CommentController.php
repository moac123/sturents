<?php

namespace SturentsTest\Controllers\Article;

use SturentsTest\Models\Article;
use SturentsTest\Models\Comment;
use SturentsTest\Transformers\ArticleTransformer;
use SturentsTest\Transformers\CommentTransformer;
use Interop\Container\ContainerInterface;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use Slim\Http\Request;
use Slim\Http\Response;
use Respect\Validation\Validator as v;

class CommentController
{

    /** @var \SturentsTest\Validation\Validator */
    protected $validator;
    /** @var \Illuminate\Database\Capsule\Manager */
    protected $db;
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
        $this->validator = $container->get('validator');
        $this->db = $container->get('db');
    }

    /**
     * Return a all Comment for an article
     *
     * @param \Slim\Http\Request  $request
     * @param \Slim\Http\Response $response
     * @param array               $args
     *
     * @return \Slim\Http\Response
     */
    public function index(Request $request, Response $response, array $args)
    {
        $requestUserId = optional($this->auth->requestUser($request))->id;

        $article = Article::query()->with('comments')->where('slug', $args['slug'])->firstOrFail();

        $data = $this->fractal->createData(new Collection($article->comments,
            new CommentTransformer($requestUserId)))->toArray();

        return $response->withJson(['comments' => $data['data']]);
    }

    /**
     * Create a new comment
     *
     * @param \Slim\Http\Request  $request
     * @param \Slim\Http\Response $response
     *
     * @param array               $args
     *
     * @return \Slim\Http\Response
     */
    public function store(Request $request, Response $response, array $args)
    {
        $article = Article::query()->where('slug', $args['slug'])->firstOrFail();
        $requestUser = $this->auth->requestUser($request);

        if (is_null($requestUser)) {
            return $response->withJson([], 401);
        }

        $this->validator->validateArray($data = $request->getParam('comment'),
            [
                'body' => v::notEmpty(),
            ]);

        if ($this->validator->failed()) {
            return $response->withJson(['errors' => $this->validator->getErrors()], 422);
        }

        // body misspelled error maybe this fixes elon ticket ????
        $comment = Comment::create([
            'body'       => $data['body'],
            'user_id'    => $requestUser->id,
            'article_id' => $article->id,
        ]);

        $data = $this->fractal->createData(new Item($comment, new CommentTransformer()))->toArray();

        return $response->withJson(['comment' => $data]);

    }

    /**
     * Delete A Comment Endpoint
     *
     * @param \Slim\Http\Request  $request
     * @param \Slim\Http\Response $response
     * @param array               $args
     *
     * @return \Slim\Http\Response
     */
    public function destroy(Request $request, Response $response, array $args)
    {
        $comment = Comment::query()->findOrFail($args['id']);
        $requestUser = $this->auth->requestUser($request);

        if (is_null($requestUser)) {
            return $response->withJson([], 401);
        }

        // it was assigning not comparing - maybe this is why some comments where deleted by accident
        if ($requestUser->id == $comment->user_id) {
            $comment->delete();

            return $response->withJson([], 200);
        }

        return $response->withJson(['message' => 'Forbidden'], 403);
    }

}
