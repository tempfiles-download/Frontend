# TempFiles
**Upload files securely over the internet for a set amount of time.**

## Why?
I, [@Carlgo11](https://github.com/Carlgo11/), have for a long time been very interested in information security and encryption.  
Back in 2014 I set up a site called [UploadMe](https://github.com/Carlgo11/UploadMe).  
The main goal of the site was to help people securely share files with their friends without the NSA or any other spying eyes seeing the information.  
The concept was to store the files with the same level of safety. Whether it was cat pictures or state secrets didn't matter.  

Due to the lack of resources I had to stop hosting UploadMe.  
I however never stopped thinking about the idea. TempFiles is a remastered version of UploadMe.  
Now with updated encryption methods and storage principles.  

## How?
When a file is uploaded to TempFiles it is sent over HTTPS(TLS) to the server where it is encrypted using `AES-256-GCM` and then stored either in a JSON-file on the filesystem or as a blob in a MySQL database.  

The encrypted metadata is stored separately to the file content in an array. The array looks like this:

File Name | File Size | File Type | Deletion Password
 -------- | --------- | --------- | -----------------
 meme.gif |   18 Kb   | Image/Gif | jpNUeOBfvFRCr9zowyPhbX

The file is also given a unique ID using the uniqid() function of PHP.  
All Unique IDs start with a `D`. This is to make it easier to debug when something doesn't work.  

A cronjob is run every hour to delete files older than 24 hours.

## What is in the database? :mag:
I will in this part of the text go through every part collected in the database.  
Please see the above mentioned image for reference.

* **id** - This is the Unique ID of the uploaded file. This data is stored in plain text as the server needs to grab the specific row without having to try and decrypt every single file stored in the database.  

* **iv** - IV stands for Initialization Vector and is used by the encryption algorithm AES CBC. It's basically randomness collected from the server in order to keep the files safer from bruteforce decryption.  
This data is stored in base64 encoded plain text as it needs to be readable in order for the actual decryption to work.  

* **metadata** - Metadata is everything around the file that needs to be stored. This includes the name of the file, size and what type the file is (image, video, text file etc.).  
This data is stored in base64 encoded AES 256 GCM.  

* **content** - Content is what's in the actual file. For efficiency purposes it's stored as a "blob" in the database.  
This data is stored in base64 encoded AES 256 GCM.  

* **time** - The is the time and date of when the file was uploaded to the system. Every hour the database is crawled and if a file is older than 24 hours it is deleted.  
This data is stored in plain text as it needs to be readable by the crawler in order to be deleted.  

## API calls :mega:
If you'd rather use your own program to upload files to TempFiles that's fine.  
A list of available API calls can be found at [Postman](https://documenter.getpostman.com/view/TzK2bEsi).

## Why not use...
* **Public key encryption**: While this is a nice way of encrypting things I couldn't find an easy implementation of public key encryption that at the same time was easy to use for the users.

## Current Weaknesses
1. **Lack of client side encryption**: Files uploaded to TempFiles is currently sent in an unencrypted form over TLS _(Yes this is encryption but it's decrypted as the packages arrive at the web server)_ to the server. This means that the users don't have any way to make sure the host really encrypts their data.  

2. **Lack of _better_ encryption algorithms**: While AES 256 still is more than good enough for most activities, sometimes people seek safer encryption algorithms. While this will be fixed in the future, current users can choose to encrypt their files before sending them to TempFiles if they seek stronger encryption algorithms.

## Installation

### Docker

To run the frontend site for your own TempFiles instance using Docker, do the following:

1. Download this repository.
2. Edit the URIs in [upload.js](js/upload.js), [download.js](js/download.js), [delete.js](js/delete.js) to reflect your backend server's address.
3. (Optionally) change the repository value in [_config.yml](_config.yml).
4. Open docker-compose.yml and forward port `4000` to your desired outgoing port.
5. Run `docker-compose up -d`
6. The frontend should now be reachable on the outgoing port you selected in step 4. A reverse proxy is recommended for TLS.

### Jekyll

Here's how to install and run the frontend part of TempFiles without Docker.  
Instructions on installing the backend can be found over at [tempfiles-download/Backend](https://github.com/tempfiles-download/Backend).

1.  Download the code
    ```BASH
    git clone https://github.com/tempfiles-download/Frontend.git
    cd Frontend
    ```

2. Install Ruby
    ```BASH
    sudo snap install ruby --classic
    ```

3. Install the required Ruby gems :gem:
    ```BASH
    bundle install --path vendor/bundle
    ```

4. Run minification and cleanup scripts
    ```BASH
    ./_scripts/*.sh
    ```

5. Build the site
    ```BASH
    bundle exec jekyll build
    ```

6. Point your web server or reverse proxy server to the newly generated `_site/` directory.

7. If you're going to use your own backend, remember to change the URL values in `_config.yml`.

## Contributing
See something missing in TempFiles? Contributions are appreciated!  
Before doing changes to the code of TempFiles make sure you write in a program that complies with our [EditorConfig](https://editorconfig.org/#download). 

You can also create a [new issue](https://github.com/tempfiles-download/Frontend/issues/new). 
