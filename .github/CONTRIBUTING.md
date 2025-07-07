# Contributing to BibTex Parser

First, thank you for reaching here.

## Issues

If you want to report bugs, ask for enhancements, or other requests, [open an issue].

## Contributing with code

### Requirements

- [Git]
- [PHP]
- [Composer]

### Usual workflow

1. [Fork this project]
2. Clone the fork to your machine
    ```bash
    git clone git@github.com:YOUR_USERNAME/bibtex-parser.git
    ```
3. Get inside the project
    ```bash
    cd bibtex-parser
    ```
4. Install dependencies
    ```bash
    composer install
    ```
5. Check it
    ```bash
    php vendor/bin/php-cs-fixer check
    php vendor/bin/phpstan
    php vendor/bin/phpunit
    ```
    - If something goes wrong, please, [open an issue].
6. [Create a branch]
7. Do your magic
8. Check your changes
    ```bash
    php vendor/bin/php-cs-fixer check
    php vendor/bin/phpstan
    php vendor/bin/phpunit
    ```
9. If everything goes well, [create a pull request]

[composer]: https://getcomposer.org/
[create a branch]: https://docs.github.com/en/free-pro-team@latest/github/collaborating-with-issues-and-pull-requests/creating-and-deleting-branches-within-your-repository
[create a pull request]: https://docs.github.com/en/free-pro-team@latest/github/collaborating-with-issues-and-pull-requests/creating-a-pull-request-from-a-fork
[fork this project]: https://docs.github.com/en/free-pro-team@latest/github/collaborating-with-issues-and-pull-requests/working-with-forks
[git]: https://git-scm.com/
[make]: https://www.gnu.org/software/make/
[open an issue]: https://docs.github.com/en/free-pro-team@latest/github/managing-your-work-on-github/creating-an-issue
[php]: https://www.php.net/
