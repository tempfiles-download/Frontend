FROM jekyll/jekyll
COPY --chown=1000:1000 ./ /srv/jekyll/
CMD echo "Installing dependencies...";jekyll serve
