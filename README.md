# EXCHANGE RATE TOOLS

This is a test system develop using [Laravel 8.x](https://laravel.com/docs/8.x) framework to create an endpoint to used by verify and/or convert a currency  amount to another amount according exchange rates available.

The conversion is available only for the exchange rates:

- BRL → EUR
- BRL → USD
- USD → BRL
- USD → EUR
- EUR → BRL
- EUR → USD

## Requirements

 - PHP 7.4 or greater
 - MySQL 8.0 or greater (option used if external API has unavailable)
 - [Docker](https://docs.docker.com/get-docker/)
 - [Composer](https://getcomposer.org/download/)

## Setup

After downloading the files from the repository, access the path that was created and copy contents of .env.example to .env file.
```bash
cp .env.example .env
```

Execute the composer command to install dependencies 
```bash
composer install
```
If you have problems with dependencies execute composer command below
```bash
composer install --ignore-platform-reqs
```

If you use another service with port 80 or 3306, stop them because in the following steps we will start installing the application's containers and services. Run the command below.
```bash 
./vendor/bin/sail up
```
This command will be built in our application in docker containers, these containers already have PHP and MySQL in version 8. This command will be built in our application in docker containers, these containers already have PHP and MySQL in version 8. After finishing the process, it will be possible to access the environment via URL http: // localhost

It is necessary to generate a new key for the application, to do this run the command below in project path.
```bash 
docker exec -it name_of_container_app php artisan key:generate
```

When accessing http://localhost you will see the screen used to convert currencies according to the exchange rate. The result of the conversions is displayed in a table with information about the country codes referring to the operation, the rate used in the conversion, the amount converted and the date referring to the rate used.

Our endpoint will be available at URL: http://localhost/api/exchange-rates/ the available parameters are: from, to and amount can be used as follows: http://localhost/api/exchange-rates/?from=BRL&to=USD&amount=10. The order return will be a json with the conversion and the rate applied.

If you wish use database to save historical data the next steps is necessary.
```bash 
docker exec -it name_of_container_database bash #access terminal container
apt update #update repository
apt install apt-utils #install utils library
apt update #update repository
apt install net-tools #insatll tools to view network configurations
```

After this execute command to view IP address to connect database.
```bash 
ifconfig
```

The result will be something like below
```bash
eth0: flags=4163<UP,BROADCAST,RUNNING,MULTICAST>  mtu 1500
        inet 172.21.0.2  netmask 255.255.0.0  broadcast 172.21.255.255
        ether 02:42:ac:15:00:02  txqueuelen 0  (Ethernet)
        RX packets 660  bytes 9379753 (8.9 MiB)
        RX errors 0  dropped 0  overruns 0  frame 0
        TX packets 576  bytes 40862 (39.9 KiB)
        TX errors 0  dropped 0 overruns 0  carrier 0  collisions 0

lo: flags=73<UP,LOOPBACK,RUNNING>  mtu 65536
        inet 127.0.0.1  netmask 255.0.0.0
        loop  txqueuelen 1  (Local Loopback)
        RX packets 16  bytes 3656 (3.5 KiB)
        RX errors 0  dropped 0  overruns 0  frame 0
        TX packets 16  bytes 3656 (3.5 KiB)
        TX errors 0  dropped 0 overruns 0  carrier 0  collisions 0
```

Device eth0 inet IP is the address to connect in database, he change always when database container restart. Edit the .env file and include this IP in the DB_HOST variable. Our laravel application now has a database connection.
Exit database terminal and execute command:
```bash
docker exec -it name_of_container_app php artisan db:create exchange_rate
```
This command will be create a new database in server. If you create a database with another name, be sure to change DB_DATABASE variable in .env file.

Let's create the table that will be used to store the history of our exchange rates.
```bash
docker exec -it name_of_container_app php artisan migrate:install
docker exec -it name_of_container_app php artisan migrate
```

After these steps, the application deployment is complete. Thanks.