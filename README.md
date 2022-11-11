# jp-epg

## Installation

### Local
```
$ git clone https://github.com/rocketsoba/jp-epg
$ composer install
```

### Global
```
# This library is not available on Packagist, so you need to add repository manually.
$ composer global config repositories.jp-epg '{"type": "vcs", "url": "https://github.com/rocketsoba/jp-epg", "no-api": true}'
$ composer global require rocketsoba/jp-epg


# If you haven't add composer bin-dir to the $PATH, please configure your $PATH.
# Default composer bin-dir is "$HOME/.composer/vendor/bin"
$ export PATH=$PATH:$HOME/.composer/vendor/bin
```

## Command
```
$ bin/epg scrape --help
Description:
  Scrape EPG

Usage:
  scrape [options] [--] [<date>]

Arguments:
  date

Options:
      --channel=CHANNEL  channel (if you don't specify chahnnel, all channels are returned) [optional] [available channnels: NTV, TBS, CX, EX, TX]
  -h, --help             Display help for the given command. When no command is given display help for the list command
  -q, --quiet            Do not output any message
  -V, --version          Display this application version
      --ansi|--no-ansi   Force (or disable --no-ansi) ANSI output
  -n, --no-interaction   Do not ask any interactive question
  -v|vv|vvv, --verbose   Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```
