# RDKit PHP

Cheminformatics for PHP, powered by [RDKit](https://github.com/rdkit/rdkit)

[![Build Status](https://github.com/ankane/rdkit-php/actions/workflows/build.yml/badge.svg)](https://github.com/ankane/rdkit-php/actions)

## Installation

Run:

```sh
composer require ankane/rdkit
```

Add scripts to `composer.json` to download the shared library:

```json
    "scripts": {
        "post-install-cmd": "RDKit\\Vendor::check",
        "post-update-cmd": "RDKit\\Vendor::check"
    }
```

And run:

```sh
composer install
```

## Getting Started

Create a molecule

```php
use RDKit\Molecule;

$mol = Molecule::fromSmiles('c1ccccc1O');
```

Get the number of atoms

```php
$mol->numAtoms();
```

Get substructure matches

```php
$mol->match(Molecule::fromSmarts('ccO'));
```

Get fragments

```php
$mol->fragments();
```

Generate an SVG

```php
$mol->toSvg();
```

## Fingerprints

A number of [fingerprints](https://www.rdkit.org/docs/RDKit_Book.html#additional-information-about-the-fingerprints) are supported.

RDKit

```php
$mol->rdkitFingerprint();
```

Morgan

```php
$mol->morganFingerprint();
```

Pattern

```php
$mol->patternFingerprint();
```

Atom pair

```php
$mol->atomPairFingerprint();
```

Topological torsion

```php
$mol->topologicalTorsionFingerprint();
```

MACCS

```php
$mol->maccsFingerprint();
```

You can use a library like [pgvector-php](https://github.com/pgvector/pgvector-php) to find similar molecules. See an [example](https://github.com/pgvector/pgvector-php/blob/master/examples/rdkit/example.php).

## Updates

Add or remove hydrogen atoms

```php
$mol->addHs();
$mol->removeHs();
```

Standardize

```php
$mol->cleanup();
$mol->normalize();
$mol->neutralize();
$mol->reionize();
$mol->canonicalTautomer();
$mol->chargeParent();
$mol->fragmentParent();
```

## Conversion

SMILES

```php
$mol->toSmiles();
```

SMARTS

```php
$mol->toSmarts();
```

CXSMILES

```php
$mol->toCXSmiles();
```

CXSMARTS

```php
$mol->toCXSmarts();
```

JSON

```php
$mol->toJson();
```

## Reactions

Create a reaction

```php
use RDKit\Reaction;

$rxn = Reaction::fromSmarts('[CH3:1][OH:2]>>[CH2:1]=[OH0:2]');
```

Generate an SVG

```php
$rxn->toSvg();
```

## History

View the [changelog](https://github.com/ankane/rdkit-php/blob/master/CHANGELOG.md)

## Contributing

Everyone is encouraged to help improve this project. Here are a few ways you can help:

- [Report bugs](https://github.com/ankane/rdkit-php/issues)
- Fix bugs and [submit pull requests](https://github.com/ankane/rdkit-php/pulls)
- Write, clarify, or fix documentation
- Suggest or add new features

To get started with development:

```sh
git clone https://github.com/ankane/rdkit-php.git
cd rdkit-php
composer install
composer test
```
