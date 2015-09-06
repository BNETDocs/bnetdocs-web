Error Reference
===============

All of the following errors are subclassed from the `BNETDocsException` class.

| Error Code | Error Name                     | Error Message                                        |
| ---------- | ------------------------------ | ---------------------------------------------------- |
| 1          | `ServiceUnavailableException`  | BNETDocs is currently offline                        |
| 2          | `ClassNotFoundException`       | Required class `$className` not found                |
| 3          | `ControllerNotFoundException`  | Unable to find a suitable controller given the path  |
| 4          | `TemplateNotFoundException`    | Unable to locate template required to load this view |
| 5          | `DatabaseUnavailableException` | All configured databases are unavailable             |
| 6          | `QueryException`               | `$message`                                           |
| 7          | `UserNotFoundException`        | User not found                                       |
