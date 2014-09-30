Subtoshi
------------

Voting branch where I am working on voting for coins to add feature --Pierce

Two branches: dev and master.  Only push to dev, and I (Pierce) can deploy the dev branch to port 8083 (for testing) on subtoshi.com when asked.  The most recent dev branch must be live on port 8083 for testing for 48 hours before a merge with master is acceptable.

Notes (feel free to add stuff here):

Subtoshi runs off of a cold and hot wallet setup.  Most of the coins are stored in cold wallets, where they cannot be withdrawn.  A small portion of the coins is stored in hot wallets, where they can be withdrawn.  The hot wallets are manually refilled by myself.  Also, as an added note, when a withdrawal fails, it probably means the hot wallet is empty.

There is a file that is gitignored that has all sensitive data like passwords.  If you need to access something, require_once($_SERVER['DOCUMENT_ROOT']."/sensitivedata/data.php"); which has all sensitive data defined as constants such as MYSQL_SELECT_USER, MYSQL_SELECT_PASSWORD, BLOCKCHAIN_INFO_API_KEY, COIN_SERVER_IP etc.  A complete list is coming soon.
