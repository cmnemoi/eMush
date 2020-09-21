# Mush Temporary Repository

This is a temporary repository for the Mush remake project, until it can get forked to the main ET repository.

If you wish to test the existing code, the "main" file that will launch the test server is node_testfile.js



#Environement: 
Require Node v14

copy .env.dist to .env

Adjust the database configuration (if required)

#Using the database

With docker:
```
docker run --name mush_mariaDb -p 3306:3306 -e MYSQL_ROOT_PASSWORD=password -e MYSQL_DATABASE=mush -d mariadb
```

# Run dev environement

```
npm run dev
```
