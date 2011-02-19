<?

    $myUserName = trim(shell_exec('whoami'));
    if($myUserName == '') $myUserName = 'root';
    chdir(dirname(__FILE__));
    chdir('../');
    chdir('../');

?>
<h1>More Information about cron</h1>

<h2>Why do I need this?</h2>
<div>
  Your Hubbub server needs to talk to other Hubbub servers periodically to exchange information
  such as status updates from your friends. Normally, your server just sits around patiently
  until you (or someone else) accesses it through a web browser, it has no initiative of its
  own. To communicate with other Hubbub servers, your server needs to know that you want it to
  activate itself at a regular interval to check what is new in the world and to download new
  updates from other Hubbub servers. Your server has a so-called <a href="http://en.wikipedia.org/wiki/Cron" target="_blank">crontab</a> 
  that enables it to do this. This article covers how you can use cron to get your Hubbub
  server up and running.
</div>

<h2>Does My Server Support Cron?</h2>
<div>
  Most hosting providers offer cron support for their customers. Log into the admin backend
  of your server and look around for entries labeled "cron", "cron jobs" or similar. If your
  hosting provider does not give you access to your servers crontab, you can use one of many
  web-based cron services.
</div>

<h2>From the Command Line</h2>
<div>
  If you have shell (command line) access to your server, you can edit your crontab by
  entering a command like
  <pre>
    crontab -e
  </pre>
  or
  <pre>
    sudo nano /etc/crontab
  </pre>
  but do this only if you have basic knowledge about using the command line interface to
  edit text files! Once the file is open, insert a line that periodically activates your
  Hubbub server. Here is the general template:
  <pre>
    * * * * * <your-username> php -f <yourPath>/cron.php > /dev/null 2>&1
  </pre>
  On this particular server, the line will probably look like this:
  <pre>
    * * * * * <?= $myUserName  ?> php -f <?= getcwd() ?>/cron.php > /dev/null 2>&1
  </pre>
</div>

<h2>From Your Admin Interface</h2>
<div>
  If your provider offers an admin interface where you can configure your cron jobs,
  enter the path to your Hubbub cron script there and set the interval to the shortest
  one possible. On this particular server, the script name would be:
  <pre>
    <?= getcwd() ?>/cron.php
  </pre>
  For example, the cron interface provided by <a href="http://mediatemple.net" target="_blank">MediaTemple</a> looks like this:
  <div>
    <img src="mt-cronedit-example.png"/>
  </div>
  Sadly, many providers severely restrict the minimum interval you can set despite the 
  fact that a single call to the Hubbub cron.php module does not need more computing power
  than an average page visit. If the allowed interval is larger than 10 minutes (for example,
  the German provider DomainFactory allows only one cron call per day) you might want to consider
  using a web-based cron service instead.
</div>