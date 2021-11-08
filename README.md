Laravel Coding Test

This is a basic Laravel skill test. You won't need to do any frontend / blade templating for this test. We will only focus on backend functionality with appropriate features and unit tests. To clarify, please do not treat this test app as a stateless API / SPA backend project. Instead, this should be a standard server side rendered, multi-page webapp, where your task is to implement the backend features only.

Let's create a small e-commerce test application. Please follow SOLID, DRY, YAGNI methods and terms. You should use Laravel 8, model factory (legacy), Faker etc. Write the following tests within the "/tests" folder. Use PostgreSQL for the database and Redis for the cache driver. Please provide the final .env file.

User model:
-- id (BigInt)
-- username (VarChar, Unique)
-- email (VarChar, Unique)
-- created_at (dateTime)
-- updated_at (dateTime)

Product model:
-- id (BigInt)
-- title (VarChar)
-- user_id (reference to user ID)
-- price (Integer)
-- created_at (dateTime)
-- updated_at (dateTime)
-- deleted_at (default soft delete)

Here are the model relations we need in the application:
# A Product must belong to a User
# A User can have many Products

## User related tests:

- Write Test: User profile remains at the top level slug, i.e. https://example.com/{username}. Write a test that, during the user account creation, one can not use a username that conflicts with existing predefined fixed route slugs (i.e. /about, /contact, etc.) in a dynamic way.

- Write Test: Write User model query scope (orderByProductsTotalValue) that returns the total price value of all products belonging to each user. Please use scope and minimum 3 users with minimum 6 products for this ordering scope. We want to see which user has the highest total worth of products. 

For example: 
1st user will be: 1 x $2000 = $2000 (price vlaue of one product)
2nd user that has 2 products: (1 x $500) + (1 x $1000) = $1500 (price value of all products)


## Product related Tests:

- Write Test: A query scope for Products, that can return products within a price range. For example it should return products only between the $100 to $200 range. Add an extra parameter for sorting by ASC or DESC (ASC default).

## Cache related Test:

- Write Test: In the user model write a method that will return data from cache or make a database call on expiration/missing. Use method name 'orderByProductsTotalValueFromCache' and source data should be the scope 'orderByProductsTotalValue' data. Cache should persist 45 minutes only.


Keep in mind, you do not need to do any css/js/blade templating. All of these tests can done through headless backend features only. 

Good luck!



