# Leaf Password Helper

This is another new feature added in version 2. This helper simply helps create and manage passwords, encrypt and verify without any security concerns.

## Helper Methods

### salt

Salt basically provides extra security for your passwords, making even weak passwords a pain for hackers to crack. Of course, passwords are already salted when using `password_hash` which comes by default with PHP, however, for other encryption methods like `md5` and `base64` as well as other direct hashing methods, no such system is in place. This method creates that security for even these encryption methods.

The salt method can both be used to set and get the password salt.

```js
$password = new Leaf\Helpers\Password;
$password->salt("THIS IS MY SALT");
```

This set's the password salt which will be encrypted based on the hash chosen by the user.

```js
$salt = $password->salt();
```

This returns the password salt.

### hash

This method basically creates a password hash. It takes in 3 parameters:

- The password to encrypt
- The encryption hash
- An array of options for the password hash

```js
$hash = Leaf\Helpers\Password::hash("USER_PASSWORD", $password::BCRYPT);
```

The most commonly used hashes, BCRYPT and Argon2 are accessible on the Password Helper object as `$password::BCRYPT` and `$password::ARGON2`.

The final options array differs based on the hash you're using. ee the [password algorithm constants](https://secure.php.net/manual/en/password.constants.php) for documentation on the supported options for each algorithm.

**Note that this is a static method, for this reason, it can be used without initialisation, that's why `Leaf\Helpers\Password::hash` was used although `$password->hash` is still valid as long as you initialise it with `$password = new Leaf\Helpers\Password`.

### verify

Verifying a user’s password has been made really simple thanks to the `verify()` method. Simply pass the plaintext password supplied by the user and compare it to the stored hash, like so:

```js
if (Leaf\Helpers\Password::verify($password, $hash)) {
    // handle user login here
}
```

### argon 2

Argon2 is one encryption method heavily used by a lot of developers. Although creating and verifying passwords with argon2 is nothing difficult, Leaf makes it even simpler with methods targetting only argon. Let's see how thses work.

#### argon2

This is a simply method used to create an Argon2 hash for your password. It takes in 2 parameters, the password to encrypt and the options for the hashing.

```js
$hash = Leaf\Helpers\Password::argon2($password, $options);
```

The options parameter is optional, but in case you want to set your own options, see the [password algorithm constants](https://secure.php.net/manual/en/password.constants.php) for documentation on the supported options for Argon2.

### argon2_verify

This method simply checks the validity of an Argon2 hash. It takes in one option, the password to verify.

```js
if (Leaf\Helpers\Password::argon2_verify($password)) {
    // handle user login here
}
```

## CRUX Encryption

Crux, instead of a functionality, is more of a new "designed" added in the Encryption Helper. Crux is a one-way encryption method that takes produces a hash from pairing a weaker, faster encryption method with a stronger but slower one, eg: Pairing up BCRYPT with MD5 or ARGON2 with BASE64. To handle such operations, Leaf has prepared the `crux` method.

### crux

```js
$password->crux($data, $password->ARGON2, $password->MD5);
```

We'd always advice you to use MD5 as the second encryption method because, MD5 strings are shorter and much easier to work with, compared to a lot of other encryption methods.
