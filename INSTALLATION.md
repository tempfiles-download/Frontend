# Installing TempFiles backend yourself

## Requirements

To install TempFiles you first need to install a webserver, php and mysql.  
In this installation I will use nginx as the webserver. Nginx also requires php-fpm.  
If you don't use Nginx then you don't have to install php-fpm.

```bash
sudo apt update
sudo apt upgrade
sudo apt install nginx php php-fpm php-mysql git
```

## Installation


### Frontend configuration
To start with, let's go to your web directory. In my case it's `/var/www/`.  
Then we'll clone this git repository and place it in the `tempfiles` directory.  

```bash
cd /var/www
git clone https://github.com/Carlgo11/TempFiles.git tempfiles
```
If the download was successful you should find the frontend in the `tempfiles/frontend/` directory.
```bash
cd tempfiles/frontend
```

When we're here we're going to install Jekyll that the frontend runs on.
```bash
sudo apt install libffi-dev nodejs python-dev gcc ruby rails make zlib1g-dev ruby-dev libcurl3
gem install bundler
bundler install
bundle exec jekyll build
```

If that succeeds you should see a new directory called `tempfiles/frontend/_site`.  
This is the directory that will be used by Nginx.  


### MySQL installation & configuration
In this tutorial MySQL will be used however you can use mariadb instead if desired.
```bash
sudo apt install mysql
```
Upon installation MySQL will ask for a password. Remember this for later!  
![MySQL Password input](https://cloud.githubusercontent.com/assets/3535780/25774895/c03b5a3c-3298-11e7-94ac-e10cc4d92b39.png)  
<br>


Now let's continue with configuring MySQL.

First we need to run the installation script for the TempFiles database. It's located in `/resources/install_mysql.sql`.  
To run it, go the that directory and do:

```bash
mysql -u root -p < install_mysql.sql
```

and enter your password you created back when we installed MySQL and press enter.  
*(You won't see your password when you type it)*

Now let's add the MySQL user that TempFiles will use.

```mysql
mysql -u root -p
CREATE USER 'tempfiles'@'localhost' IDENTIFIED BY '<password>';
grant all privileges on tempfiles.files to `tempfiles`@`localhost`
```

When you're done with that, let's make sure the settings are used by MySQL.  

```mysql
flush privileges;
exit;
```

#### Advanced MySQL permissions
The MySQL user only needs _SELECT_, _INSERT_, _UPDATE_, _DELETE_ permissions to the `files` table.




### Nginx configuration


Now let's configure Nginx!  
First we have to go to the directory where vhosts are read from  
In Nginx' case that's  `/etc/nginx/sites-available/`.

```bash
cd /etc/nginx/sites-available/
```

Now let's configure a vhost config.  
I'll edit the default one since I don't have anything else on my server but if you plan to host multiple websites on your webserver you shouldn't use default.  
Instead search for how to configure multiple vhosts on Nginx.    

Now since we don't want the old config we'll clear the `default` file and paste in our own config.  
`{web directory}` is the path that you installed tempfiles to earlier.
  
```bash
sudo cat /etc/nginx/sites-available/default < {web directory}/tempfiles/resources/nginx-site.conf
```

Edit the MySQL username and password to those set earlier. (database and hostname is optional)  
If you want HTTPS on your server (which is recommended) this is also the time to configure that.

Now let's restart Nginx.
```bash
sudo systemctl restart nginx
```

If that works you should be able to see the default TempFiles page when visiting your website.
![tempfiles default page screenshot](https://cloud.githubusercontent.com/assets/3535780/25774924/23c9539c-3299-11e7-9e3c-fe72f30abf4b.png)

That's it! :smile:
