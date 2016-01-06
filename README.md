# Cronalytics.io PHP CLI Intergration
[https://cronalytics.io] is a SaaS to help track, monitor and report on regular task (cron jobs).
This repositry contains a PHP application that is will:
1. notify cronalytics of a job starting
2. run the script
3. notify cronalytics of the job finishing
4. send the script output to stdout/stderr

## Getting Started

You will need to have php already setup on your system and working via the command line.
Then just run composer to get dependancies and it is ready to go.
```
composer install
```


## Usage

1. Goto [https://cronalytics.io/setup] and create a new cron job.
1. copy the private hash
1. open crontab ```crontab -e```
1. add/update your command to pass it through this script
  - Just add the following before your existing script, dont forget quotes around your script
```
php /path/to/repo/src/cronalytics.php <private cron hash> "<script>"
```