# TempFiles
Upload files securely over the internet for a set time.

## Why?
I ([@Carlgo11](https://github.com/Carlgo11/)) have for a long time been very interested in information security and encryption.  
Back in 2014 I set up a site called [UploadMe](https://github.com/Carlgo11/UploadMe) (UploadMe.se).  
The main goal of UploadMe was to help people securely share files with their friends without the NSA or any other spying eyes seeing the information.  
The concept was to store the files with the same level of safety. Wheter it was cat pictures or state secrets didn't matter.  

Due to the lack of resources I had to stop hosting UploadMe.  
I however never stopped thinking about the idea. TempFiles is a remastered version of UploadMe.  
Now with updated encryption methods and storage principles.  


## How?

When a file is uploaded to TempFiles it is sent over HTTPS(TLS) to the server where it is encrypted using `AES 256 CBC` and then stored as a blob on MySQL.  
All metadata is also encrypted and stored there until someone requests to download it.  

```
TODO: INSERT IMAGE
```
The metadata is stored seperately to the file content in an array. The array looks like this:

File Name | File Size | File Type
 -------- | --------- | --------- 
meme.gif | 18 Kb | Image/Gif

The file is also given a unique ID using the uniqid() function of PHP.  
All Unique IDs start with a `D`. This is to make it easier to debug when something doesn't work.  

After the file has been encrypted and sent to the database it looks like this:
![database](https://puu.sh/pYrQL/4ed9be2137.png)

For the deletion of old files I chose MySQLs event scheduler just because it's easy to use.
It's set to delete files older than 24 hours.

## Why not use `X`?

* __JavaScript encryption__: I chose not to use this because (1) I don't know that much JavaScript and (2) Tor browsers generally don't support JavaScript.  
I may rethink this at a later time as it would mean client side encryption.

* __Public key encryption__: While this is a nice way of encrypting things I couldn't find an easy implementation of public key encryption that at the same time was easy to use for the users.

## Current Weaknesses

1. __Lack of client side encryption:__ Files uploaded to TempFiles is currently sent in an unencrypted form over TLS _(Yes this is encryption but it's decrypted as the packages arrive at the web server)_ to the server. This means that the users don't have any way to make sure the host really encrypts their data.
2. __Larger files can't be uploaded:__ Because of the limitations of MySQL, PHP and web browsers (Without JS) it's very hard to send large files over the internet. The current limit on files is 2MB.
3. __Lack of _better_ encryption algorithms:__ While AES 256 still is more than good enough for most activities, sometimes people seek safer encryption algorithms. While this will be fixed in the future, current users can choose to encrypt their files because sending them to TempFiles if they want better encryption algorithms.
