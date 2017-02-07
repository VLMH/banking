# Requirement

- php 5.5
- composer
- sqlite3

# Install & Config

- `$ composer install`
- set php.ini
  - find php.ini `$ php -i | grep php.ini`
  - set `always_populate_raw_post_data = -1`

# Endpoints
`$ php artisan route:list`

# Run test
`$ composer test`

# Run
`$ php artisan serve`

# Sample request
- create user
  `curl -v 127.0.0.1:8000/users --data '{"hkid": "A1234"}' -H 'Content-Type:application/json'`
- open account
  `curl -v 127.0.0.1:8000/users/1/accounts -X POST`
- show account
  `curl -v 127.0.0.1:8000/users/1/accounts/1`
- deposit
  `curl -v 127.0.0.1:8000/users/1/accounts/1/deposit --data '{"amount": 1000}' -H 'Content-Type:application/json'`
- withdraw
  `curl -v 127.0.0.1:8000/users/1/accounts/1/withdraw --data '{"amount": 500}' -H 'Content-Type:application/json'`
- transfer
  `curl -v 127.0.0.1:8000/users/1/accounts/1/transfer --data '{"targetAccountId": 2, "amount": 500}' -H 'Content-Type:application/json'`
- close account
  `curl -v 127.0.0.1:8000/users/1/accounts/1 -X DELETE`
