Edit: The following readme is OUT OF DATE and needs updating.  I will update it later.
Also, please DO NOT clone yet, I am still cleaning the repo

Folders:
Admin: Don't worry about this, it's just an admin dash I'm working on
WWW: The exchange's code

This server is operating under a 2 port setup.

Port 80 points to /var/www
Port 8083 points to /home/ubuntu/git/exchange

All changes made here will show up immediately on port 8083.  When you have made a change, or added a feature, first, commit the change

git add [the file you changed]
git commit -m "I added a changed"

Then push the change

git push origin master

Never EVER run "git add --all" as someone may be in the middle of editing a different file when you are creating a commit, and then you are also commiting some elses half changed file.  not good
TL;DR only add the files YOU changed. dont add them all

To move changes from this testing server on port 8083 to the main server on port 80 run

git add [the files you changed]
git commit -m "I added a feature"
git push origin master
sudo cp ./www/file/I/changes /var/www/file/I/changed

Also, remember this isn't a hackathon.  It is not good enough for it to "work".  All code changes must be hacker AND idiot proof.

Pushing a change does not put that change to the production server (port 80).
Changes are only pushed for version control and backup purposes.

To move a change from testing (port 8083) to production, copy that file (or directory) to /var/www

Generally, do not copy entire folders though, because if I you copy the directory as I'm making a change to another file in that directory, then that is bad

Backups are taken daily, so if you have a change that you want to backup now, just commit it to the repo

Can apache not access a file?  Are you getting "Forbidden"?
Possible problems and solutions:
permission problem.  To solve chown the file to ubuntu:www-data:
sudo chown ubuntu:www-data /var/www/myfile.php
There may be an error in the file.  check /var/log/apache2/error.log:
tail -f /var/log/apache2/error.log
You refresh spammed and are now blacklisted because of DDOS protection:
just wait a minute without opening the website, and you will be automatically de-blacklisted

If there is a typo, I'm sorry.  Nano doesn't have spell check, and this isn't an English paper

This is going to be the best exchange ever.  I would say we are changing the world, but that's too cliché

TL;DR Please read the whole thing.  This is a production level project, and requires production level work.  Read the whole README
