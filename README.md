# Sobrus Symfony API Test

This project is a Symfony-based RESTful API for managing blog articles. It includes various features such as CRUD operations, JWT authentication, file uploads, validation.

## Features

### Blog Article Management:

Create, Read, Update, and Soft Delete blog articles.
Blog articles include fields such as authorId, title, publicationDate, creationDate, content, keywords, status, slug, and coverPictureRef.
Uploaded images are stored in the public/uploaded_pictures directory.

### JWT Authentication:

Secure the API with JSON Web Tokens (JWT).
Login endpoints to authenticate users and protect certain routes.

### Validation:

Symfony’s validation component ensures data integrity, with constraints applied to article fields.

### Keyword Extraction:

A custom algorithm identifies the top 3 most frequently used words in the article content, excluding banned words, and automatically assigns them as keywords.

### Content Validation:

Banned words are dynamically loaded from a configuration file and validated against article content.

### Testing:

Unit and functional tests are written using PHPUnit to ensure robust API behavior.

### API Documentation:

Comprehensive API documentation using NelmioApiDocBundle (Swagger).
Easily visualize and test API endpoints through the documentation.

## Installation

### Clone the repository:
```
git clone https://github.com/your-username/Sobrus-symfony-api-test.git
```
### Install dependencies:
```
composer install
```
### Set up the environment variables:
```
cp .env.example .env
```
### Generate the JWT keys:
```
php bin/console lexik:jwt:generate-keypair
```
### Run the database migrations:
```
php bin/console doctrine:migrations:migrate
```
### Start the Symfony server:
```
symfony serve
```

## API Endpoints
The API provides the following endpoints for blog article management:
- POST /api/blog-articles: Create a new blog article.
- GET /api/blog-articles: Retrieve all blog articles.
- GET /api/blog-articles/{id}: Retrieve a single blog article by ID.
- PATCH /api/blog-articles/{id}: Update a blog article by ID.
- DELETE /api/blog-articles/{id}: Soft delete a blog article by ID.

## Authentication
- POST /api/login_check: Authenticate and retrieve a JWT token.

## Testing
Run the tests using PHPUnit:
```
php bin/phpunit
```
## Documentation
The API is documented using Swagger and can be accessed at:
```
/api/doc
```


**Thank you** 😉
