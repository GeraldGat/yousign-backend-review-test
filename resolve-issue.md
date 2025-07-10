# Encountered issues
This file aim to answer the most common issues that users might encounter when using this project.

## No such file or directory when using `make start`
### Issue
When using `make start` you might encounter the following error :
```sh
make db
make[1]: Entering directory '.../yousign-backend-review-test'
[12:22:57][db] Preparing db ...
': No such file or directory
```
### Solution
This issue seems to appear if you use Windows with WSL and come from the file `bin/console` and/or `Makefile` containing `CRLF` line endings. You can fix this by running the following command in your terminal:
```sh
dos2unix bin/console Makefile
```
