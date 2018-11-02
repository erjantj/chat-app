# Chat app

This is simple chat app on PHP using websockets.

# Dependencies

PHP >= 5.6

*   **Lumen:** Framework
*   **PHP Unit:** Testing
*   **jwt-auth:** JWT token library
*   **Swagger-Php:** Open Api documentation generator

# Running project

1. First of all install dependencies and run migrations.

	```
	composer install && composer migrate
	```

2. Then run websocker server for instant messaging. It will run on *http://localhost:8000*.

	```
	composer websocket
	```

3. Now you can start main app. App will start on [http://localhost:9000](http://localhost:9000).

	```
	composer start
	```
4. Client side availabe here [http://localhost:9000/client/](http://localhost:9000/client/). Go ahead and write your first message.

# Architecture

### Websocket
Because of periodically refreshing the page to poll for new messages would be very slow and eat lot of internet traffic I used websockets. App uses simple websocket server on PHP. It provides lighing fast messages delivery and smooth user experience.

### Migration
App supports migrations to easy maintain and scale database. Lumen provides out of the box support for that. 
To create new migration run:

```
php artisan make:migration MigrationName
```

To apply migration run:

```
php artisan migrate
```

### Models
There are only two models

* **User:** Stores users data.
* **Message:** Stores messages data.

### Config
All configuration fies stored in config folder. It includes also config for JWT and Websocket service. By default configs set up for local usage. To override defaults just copy `.env.example` file as `.env` and write new configs there.

### Tests
App supports unit testing. Tests folder contains all test classes. Database transactions used for databse testing, so no data will be currapted on local server. By far there is only messages test. To run tests you can use command:

```
composer test
```

# Features

### Authentication
By project requirements user registration and user login are not needed, so user can authorize just by entering username. This simplification made for ease of demonstration. After that user will get JWT token, and can make requests to server. JWT tokens are self-contained, so we don't need any session storage on server side. 

Client can pass Authorization header like this.

```
Authorization: [TOKEN_HERE]
```

### User status

User can see which user is currently online. If user leaves chat, then he becomes offline for other online users.

### Instant messaging
User can write messages to any user that is available in system. User can scroll over his old messages. User gets notification as new message arrives. Because of websocket server, messaging and online status tracking works instantaneous.

# Documentation
Project API supports versioning. 

API documentation: [http://localhost:9000/swagger/](http://localhost:9000/swagger/). 
