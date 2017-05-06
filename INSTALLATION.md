## Installing TempFiles yourself

__<span style="color:red">This is a draft and not intended to be used in production!</span>__
### Requirements

To install TempFiles you first need to install a webserver php and mysql.  
In this installation I will use nginx as the webserver. Nginx also requires php-fpm.  
If you don't use Nginx then you don't have to install php-fpm.  
### Installation
```bash
apt update
apt upgrade
apt install nginx php php-fpm
apt install mysql php-mysql git
```
MySQL will ask for a password. Remember this for later!  
![MySQL Password input](https://cloud.githubusercontent.com/assets/3535780/25774895/c03b5a3c-3298-11e7-94ac-e10cc4d92b39.png)  
<br>

Now go to your web directory. In my case it's /var/www/.  
Then we'll clone this git repository and place it in the `tempfiles` directory.  
```bash
cd /var/www
git clone https://github.com/Carlgo11/TempFiles.git tempfiles
```
Now let's configure Nginx!  
First we have to go to the directory where vhosts are read from  
I'm Nginx case that's  `/etc/nginx/sites-available`.
```bash
cd /etc/nginx/sites-available
```

Now let's configure a vhost config.  
I'll edit the default one since I don't have anything else on my server but if you plan to host multiple websites on your webserver you shouldn't use default.  
Instead search for how to configure multiple vhosts on Nginx.    

Now since we don't want the old config we'll clear the `default` file and paste in our own config.  
```bash
cat /dev/null > /etc/nginx/sites-available/default
nano default
```
Now paste in this:
```nginx
server {
        listen 80 default_server;
        listen [::]:80 default_server;

        include snippets/snakeoil.conf;

        root /var/www/tempfiles;

        index index.php;

        server_name _;

        location / {
                try_files $uri $uri/ =404;
        }

        location ~ \.php$ {
                include snippets/fastcgi-php.conf;
                fastcgi_pass unix:/run/php/php7.0-fpm.sock;
                # MySQL hostname
                fastcgi_param ag44jc7aqs2rsup2bb6cx7utc 'localhost';
                # MySQL username
                fastcgi_param hp7wz20wu4qfbfcmqywfai1j4 'tempfiles';
                # MySQL password
                fastcgi_param mom8c5hrbn8c1r5lro1imfyax 'password';
                # MySQL database
                fastcgi_param qb1yi60nrz3tjjjqqb7l2yqra 'tempfiles';
                # MySQL table
                fastcgi_param rb421p9wniz81ttj7bdgrg0ub 'files';
        }
        location /api {
                rewrite ^/(.*)+$ /api.php?$1;
        }
        location /download {
                rewrite ^/(.*)+$ /download.php?$1;
        }

}

```
Edit the MySQL username and password. (database and hostname is optional)  
If you want HTTPS on your server (which is recommended) this is also the time to configure that.

Now restart Nginx.
```bash
systemctl restart nginx
```

If everything have worked so far you should now see the text "Connection to our database failed." if you go to your website.  
![connection to our database failed](https://cloud.githubusercontent.com/assets/3535780/25774909/f334fc7c-3298-11e7-8f6c-419c4371ef47.png)
<br>

Works? Great!  
Now let's continue with configuring MySQL.

First we need to run the installation script for the TempFiles database. You should be able to find that in `/var/www/tempfiles/install_mysql.sql`
To run it do:
```bash
mysql -u root -p < /var/www/tempfiles/install_mysql.sql
```
and enter your password you created back when we installed MySQL and press enter.  
*(You won't see your password when you type it)*

Now let's add the MySQL user that TempFiles will use.
```mysql
mysql -u root -p
CREATE USER 'tempfiles'@'localhost' IDENTIFIED BY '<password>';
grant all privileges on tempfiles.files to `tempfiles`@`localhost`
```
These should be the same details that you set earlier in the `default` file.  
When you're done with that, let's make sure the settings are used by MySQL.  


__\## Note for advanced users \##__  
The MySQL user only needs permissions for _SELECT_, _INSERT_, _UPDATE_, _DELETE_.

```mysql
flush privileges;
exit;
```
Now restart Nginx again.
```bash
systemctl restart nginx
```

If that works you should be able to see the default TempFiles page when visiting your website.
![tempfiles default page screenshot](https://cloud.githubusercontent.com/assets/3535780/25774924/23c9539c-3299-11e7-9e3c-fe72f30abf4b.png)

That's it! :smile:
