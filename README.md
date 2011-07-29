Pheasant Docs
=============

This repo powers http://getpheasant.com and also provides the documentation for
Pheasant.

Developing Docs
---------------

Github's Pages are used, which are powered by Jekyll. To run locally under OSX:

```bash
sudo gem update --system
sudo gem install jekyll
sudo pip install pygments
git clone -b gh-pages git@github.com:lox/pheasant.git pheasantdoc
cd pheasantdoc
jekyll --server --pygments
```
