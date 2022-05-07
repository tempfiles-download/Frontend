# TempFiles Frontend
**Share files securely over the internet for a day.**

## Why?

I, [@Carlgo11](https://github.com/Carlgo11/), have for a long time been very interested in information security and encryption.  
Back in 2014 I set up a site called [UploadMe](https://github.com/Carlgo11/UploadMe).  

The main goal of the site was to help people securely share files with their friends without the NSA or any other spying eyes seeing the information.  
The concept was to store the files with the same level of safety regardless of whether it was cat pictures or state secrets.  

Due to the lack of resources I had to stop hosting UploadMe.  
I however never stopped thinking about the idea. TempFiles is a remastered version of UploadMe.  
Now with updated encryption methods, improved storage mechanisms and better resource management resulting in a lower hosting bill.  

## Installation

The following text describes how to install the static website (Frontend).  
For instructions on how to install the Backend service responsible for the encryption and storage of uploaded files, see [tempfiles-download/Backend](https://github.com/tempfiles-download/Backend).

### JAMstack CDN _(GitHub Pages / Cloudflare Pages)_

TempFiles Frontend is primarily built to be hosted on CDNs as a static website.  
It's therefore trivial to use GitHub Pages to host the website for free:

1. Fork this repository.
1. Edit the URLs in [_config.yml](_config.yml) to reflect your Backend server's address.
1. Select `master` as source branch and `/ (root)` as source path on __Settings__ > __Pages__
1. (Optionally) Create a `CNAME` file with your desired domain name and point your domain to `<username>.github.io`.

### Docker

To run the frontend site for your own TempFiles instance using Docker, do the following:

1. Download `docker-composer.json` and `_config.yml`.
1. Edit the URLs in [_config.yml](_config.yml) to reflect your backend server's address.
1. (Optionally) change the repository value in [_config.yml](_config.yml).
1. (Optionally) Open docker-compose.yml and forward port `4000` to your desired outgoing port.
1. Run `docker-compose up -d`.
1. The frontend should now be reachable on the outgoing port you selected in step 4. A reverse proxy is recommended for TLS.

### Jekyll

Here's how to install and run the Frontend of TempFiles locally without Docker:

1.  Download the code
    ```BASH
    git clone https://github.com/tempfiles-download/Frontend.git
    cd Frontend
    ```

1. Install Ruby
    ```BASH
    sudo snap install ruby --classic
    ```

1. Install the required Ruby gems :gem:
    ```BASH
    bundle install --path vendor/bundle
    ```

1. Run minification and cleanup scripts
    ```BASH
    ./_scripts/*.sh
    ```

1. Build the site
    ```BASH
    bundle exec jekyll build
    ```

1. Either:
  1. Point your web server or reverse proxy server to the newly generated `_site/` directory.
  1. Set up a simple web server with:
     ```BASH
     bundle exec jekyll serve
    ```

1. If you're going to use your own backend server, remember to change the URL values in `_config.yml`.

## Contributing

See something missing in TempFiles? Contributions are appreciated!  
Before doing changes to the code of TempFiles make sure you write in a program that complies with our [EditorConfig](https://editorconfig.org/#download).

You can also create a [new issue](https://github.com/tempfiles-download/Frontend/issues/new).
