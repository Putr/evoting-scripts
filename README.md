# evoting-scripts
Scripts used by election commisions for my protocol-first e-voting model as used in Slovenia

## Setup

### Install dependencies

    composer install

### Sending information 

Copy config file

    cp config.ini.example config.ini

Configure your sending information.

### Add your email list

Place your email list in the root of this project. It should be a one field CSV with only a list of emails and no header.

## Generate codes

Run code generator with 

    php generateCodes.php --count=400
 

## Send emails

Run sending with:

    php sendCodesToEmails.php --email=email_file.csv --codes=codes_file.csv --count=400 --template=ballot --subject="Please vote"

Last variable `--count` is used to double-check that the number of voters is the number of codes and emails that is expected.