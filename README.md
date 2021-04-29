# A Simple API

This API is written in the Slim framework. It has some bugs that need to be identified.

## Installation

Requires:

* composer (PHP dependency installer)
* docker
* docker-compose

First download PHP libraries required (you need some version of PHP locally, but shouldn't matter which due to the --ignore-platform-reqs directive)

```
composer install --ignore-platform-reqs
```

This should also have created the file `.env` (if not `cp .env.example .env`)

Now run

```
docker-compose up -d
```

This might take a few minutes as it downloads images to build containers.

Once that runs you can set up the database:

```
docker-compose exec db mysql -e 'create database sturents;'
docker-compose exec web vendor/bin/phinx migrate
docker-compose exec web vendor/bin/phinx seed:run
```

Now browse to http://localhost:8081 - the Slim homepage should show.

## Testing the system

There is a `docs/swagger.json` file which describes the whole API. You can view this interactively
at [Swagger editor](https://editor.swagger.io/).

The package comes with unit and functional tests. Run them all with

```
docker-compose exec web vendor/bin/phpunit
```

It also comes with a `docs/postman.json` file. [Get Postman here](https://www.getpostman.com/downloads/).

You can import the JSON file to have a premade set of requests. If you edit the collection and go to
the variables tab you may want to add the following:

```
APIURL: http://localhost:8081/api
EMAIL: some@email.com
PASSWORD: abc123
USERNAME: your-name
```

You can also add `slug` once you've created an article, to make further article requests easier.

## Requirements

This application represents an example real-world blog tool.

Like all code bases however, this code base has problems. These problems are causing live issues for users
and they have filed support tickets, which are shown below. To assist in managing these tickets the problems
are deliberately isolated to:

* Test code
* Source code
* System configuration

There are no deliberate issues in:

* Docker files
* Database structure/seed/migrations
* Documentation files

The goal of working on this application is to discover how you adapt to an unfamiliar codebase, and deal
with the ways problems may be described by users or colleagues. The system uses the [Slim](http://www.slimframework.com/docs/)
framework for most aspects, [Eloquent](https://laravel.com/docs/5.8/eloquent) for database access and
[JSON web tokens](https://jwt.io/) for authentication. It does not provide a web front end, only API endpoints.

## Support tickets

"Thanks for letting me into the beta of your blog platform. I've published some articles and am able to log in
with my password but the system tells me I have to be logged in to edit my article - but I am logged in? Please can
you help as I've spotted some typos and need to fix them."

----

"Sorry, this might be me doing something wrong but I noticed something odd on one of my articles. Yesterday morning there
were comments on my article but now there aren't any. I messaged one of my friends and asked if she deleted it and 
she said she didn't so can you tell me what's happening?"

----

"Dear sir

I am a security researcher working with Pentesting.com. I have urgent info about your site. You are vulnerable
to compromise and we can show you how. To prove I am correct here is a valid authentication token for your most
popular user, Elon Musk:

eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE1NjI5NDk1NjQsImV4cCI6MTU2Mjk1Njc2NCwianRpIjoiWkN4Tys3eGpkenVhM2xlRW00U1A4Zz09IiwiaXNzIjoiaHR0cDpcL1wvbG9jYWxob3N0Iiwic3ViIjoiZWxvbiJ9.0V0Z9TC07CrNGr1Yy-ElTuDCtesXncJ7y7zIbRmkHqo

We bill by the hour."

----

"Hi it's Dave in QA. I emailed you last week to ask if passwords were case sensitive because that meets our current
audit requirements - you told me they were. I just tried to log in with my usual password but in lower case and it
let me sign in. So please can you check that passwords have to be case sensitive because otherwise we'll get blamed
for it again."

----

"Hey well done on launching the new platform, it looks great. At the data science team we're trying to work out if 
the number of articles a user favourites can give them better reading suggestions in future. We're pulling live data
but we've noticed something strange. There are over 1,000 favourites already on Elon's first post, but no other posts
have any favourites. I know that might be true but it just seems odd right - surely some other posts should get at
least one favourite?"

----

"I think we have a problem somewhere; bunch of our original users seem to have blank emails. I know we had that 
validator issue but I thought that just stopped them editing their emails - don't think they should be able to
set them to blank or we won't be able to do password resets"

----

"There's a few new test fails come in since the latest commits to master; can you take a look at them, I think Jenkins
was down so they just got accepted"

----

"Hi, I followed Elon Musk but all he posts are cat videos; can you add an option to unfollow him - I press the button
that looks like it should do that but I guess I misunderstood because it doesn't seem to do anything"

----

"Hi

We've been working on an integration to your API to list your articles on Facebook.

We have your swagger file and are making GET requests to "/api/articles/" but it crashes the script every
time we run it - I think the requests time out or something. Are we doing something wrong or is there a bug?"

----

"Hey it's Elon. I think something broke in today's deploy - I still have a bunch of people commenting on my articles but
their comments are blank. I'm pretty sure that was in the original spec and we tested that you can't post a blank
comment but literally everyone has started doing it. I don't know why, lots of users were posting normal comments
before - have they all got hacked or is it something else?"
