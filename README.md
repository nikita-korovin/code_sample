# Code sample
This is a (high level) code sample of an error handling system I implemented (Symfony 4.4 and PHP 7.2). 

The error is "caught" in a specific situation of a user experience, then sent to a message broker, 
after which is consumed and sent to a processor (there is a lot of those) to alert the "authorities" in one of multiple ways

Many things I would do differently now:
- ErrorManager is not SOLID at all, has to be broken down into separate classes
- With newer versions of php there would be property type hinting, readonly properties / classes as DTOs, Constructor property promotions, etc
