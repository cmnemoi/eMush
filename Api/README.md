# Environement: 
Require Node v14

copy .env.dist to .env

Adjust the database configuration (if required)

# Using the database

With docker:
```
docker run --name mush_mariaDb -p 3306:3306 -e MYSQL_ROOT_PASSWORD=password -e MYSQL_DATABASE=mush -d mariadb
```

run migrations: 
```
npx typeorm migration:run
```
generate migration:
```
npx typeorm migration:generate -n init
```

# Run dev environement

```
npm run dev
```

#### Updating the database
Caution, it will drop the database
```
npm run update_database
```

# Test environement

You can set up tests environment variable in .env.test (if this file doesn't exist .env will be used)
Creating a test database is highly recommended to have a proper database dedicated to tests