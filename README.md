# PlantGeneDB
[Home Page](https://ainilaha.github.io/PlantGeneDB/)

## Run docker

#### To Start

You first need to change the directory to docker by `cd docker`
- `docker compose up -d `

#### check the containers

- `docker compose ps`

#### Access the webpages:
 http://localhost/


 ## Access the database

 #### Login into the db container

- `docker compose exec db bash`
- `mysql -u user -p` 
- type passward as set in docker-compose.yml, which is `userpss` for now.

#### access the dataase via phpmyadmin:

http://localhost:8080/