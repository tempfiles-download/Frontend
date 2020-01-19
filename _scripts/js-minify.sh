#!/bin/bash

for file in js/*.js; do
  babel-minify "$file" -o "$file" --simplifyComparisons --simplify --mangle
done
