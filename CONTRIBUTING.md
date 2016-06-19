## Getting Started
First off, thanks for having the willingness to contribute, and for taking the
time to read this document!

The **bnetdocs-web** project powers the [bnetdocs.org](https://bnetdocs.org)
website. Releases are made and deployed by [@carlbennett]
(https://github.com/carlbennett), whom also keeps a watchful eye out for any
issues or pull requests to this repository.

## GitHub Code of Conduct
### Feature requests / enhancements
All feature requests for the website must be vetted by one of the maintainers.
The maintainer will decide whether or not your request is valid and will also
either approve or deny your request. If approved, someone will integrate your
feature/enhancement into the project when possible and when time permits.

### Issues
If you have found a bug or issue with the project/website, don't hesitate to
let us know. We accept all bug reports and will handle them in a respectful
manner.

#### Submitting an issue
When you are submitting an issue, make sure you include the following:

- A screenshot of the issue you're describing.
- Steps to reproduce the issue.
- How you encountered the issue.
- What your intention was and what you expected to happen.
- If you feel it necessary, or if asked for, details about your environment.
 - Your operating system and its version number (e.g. Windows 7 Pro 64-bit).
 - Your browser and its version number (e.g. Chrome 50).

Giving us the information above will help immensely when troubleshooting your
issue on our side, and could even lead directly to a bugfix in the best
scenario.

## Project directory structure
The project is structured such that we can add tools, sample configurations,
documentation, and of course the actual code, all in the same repository.

| Path  | Description                                                         |
|-------|---------------------------------------------------------------------|
| /bin/ | Miscellaneous scripts and utilities for working with this project.  |
| /etc/ | Sample configurations and other configuration-related files.        |
| /src/ | The project itself.                                                 |
| /tmp/ | An intentionally empty directory for use with scripts.              |

Any file in the root of this repository is used by Git, GitHub, or otherwise
serves a very specific purpose.
