# Requirements
* PHP8.1 
* Qdrant installed and running (https://qdrant.tech/documentation/quick-start/)
# App setup
copy `.env.example` to .`.env` file
* put your open ai key in OPENAI_API_KEY
* put your database credentials in DB_DATABASE, DB_USERNAME and DB_PASSWORD
* run `composer install`
* run `php artisan key:generate`
* run `php artisan migrate`
* (optional) change file in storage/files, adjust file_name in ParseWordDocument (app/console/Commands)
* run `php artisan app:parse-word-document`
* run `php artisan app:generate-tags`
* run `php artisan app:generate-embeddings`
* run `php artisan app:chat` and start asking questions! Type goodbye to close chat
