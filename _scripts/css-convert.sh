#!/bin/bash

for file in css/*.css; do
  sed -i '1s/^/---\n---\n\n/' "$file"
  mv "$file" "${file%.css}.scss"
done
