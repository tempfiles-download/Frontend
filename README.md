# TempFiles
**Upload files securely over the internet for a set amount of time.**  

[![build status](https://img.shields.io/github/workflow/status/Carlgo11/TempFiles/Test%20Jekyll?style=for-the-badge)](https://github.com/Carlgo11/TempFiles/actions)

## Why?
I, [@Carlgo11](https://github.com/Carlgo11/), have for a long time been very interested in information security and encryption.  
Back in 2014 I set up a site called [UploadMe](https://github.com/Carlgo11/UploadMe).  
The main goal of the site was to help people securely share files with their friends without the NSA or any other spying eyes seeing the information.  
The concept was to store the files with the same level of safety. Whether it was cat pictures or state secrets didn't matter.  

Due to the lack of resources I had to stop hosting UploadMe.  
I however never stopped thinking about the idea. TempFiles is a remastered version of UploadMe.  
Now with updated encryption methods and storage principles.  

## How?
When a file is uploaded to TempFiles it is sent over HTTPS(TLS) to the server where it is encrypted using `AES 256 CBC` and then stored as a blob on MySQL.  
All metadata is also encrypted and stored there until someone requests to download it.  

The metadata is stored separately to the file content in an array. The array looks like this:

File Name | File Size | File Type | Deletion Password
 -------- | --------- | --------- | -----------------
 meme.gif |   18 Kb   | Image/Gif | jpNUeOBfvFRCr9zowyPhbX

The file is also given a unique ID using the uniqid() function of PHP.  
All Unique IDs start with a `D`. This is to make it easier to debug when something doesn't work.  

After the file has been encrypted and sent to the database it looks like this:
![database](https://user-images.githubusercontent.com/3535780/72116323-3d769700-334a-11ea-9fd0-78b455a773f6.png)

For the deletion of old files I chose MySQLs event scheduler just because it's easy to use.
It's set to delete files older than 24 hours.

## What is in the database?
I will in this part of the text go through every part collected in the database.  
Please see the above mentioned image for reference.

* **id** - This is the Unique ID of the uploaded file. This data is stored in plain text as the server needs to grab the specific row without having to try and decrypt every single file stored in the database.  

* **iv** - IV stands for Initialization Vector and is used by the encryption algorithm AES CBC. It's basically randomness collected from the server in order to keep the files safer from bruteforce decryption.  
This data is stored in base64 encoded plain text as it needs to be readable in order for the actual decryption to work.  

* **metadata** - Metadata is everything around the file that needs to be stored. This includes the name of the file, size and what type the file is (image, video, text file etc.).  
This data is stored in base64 encoded AES 256 CBC.  

* **content** - Content is what's in the actual file. For efficiency purposes it's stored as a "blob" in the database.  
This data is stored in base64 encoded AES 256 CBC.  

* **time** - The is the time and date of when the file was uploaded to the system. Every hour the database is crawled and if a file is older than 24 hours it get's deleted.  
This data is stored in plain text as it needs to be readable by the crawler in order to be deleted.  

## API calls
If you'd rather use your own program to upload files to TempFiles that's fine.  
Below are a list of public API calls, available for everyone to use, along with cURL usage templates.  
  
_Words surrounded by `{}` are variables that should be changed by the user before the command is sent._

More a complete list of API calls, have a look over at [Postman](https://documenter.getpostman.com/view/1675224/SW7ezkZn).

## Why not use `X`?
* **Public key encryption**: While this is a nice way of encrypting things I couldn't find an easy implementation of public key encryption that at the same time was easy to use for the users.

## Current Weaknesses
1. **Lack of client side encryption**: Files uploaded to TempFiles is currently sent in an unencrypted form over TLS _(Yes this is encryption but it's decrypted as the packages arrive at the web server)_ to the server. This means that the users don't have any way to make sure the host really encrypts their data.  

2. **Lack of _better_ encryption algorithms**: While AES 256 still is more than good enough for most activities, sometimes people seek safer encryption algorithms. While this will be fixed in the future, current users can choose to encrypt their files before sending them to TempFiles if they seek stronger encryption algorithms.
