# Simple Engage Service GLPI plugin

##### _Make it easy, simple and imagine_

<p align="center">
  <img src="https://raw.githubusercontent.com/miguelanruiz/engage/master/engage.svg" alt="Simple Engage Service"/>
</p>

## Features

- Set a technical user for welcome messages 
- Pick-up time by the assigned technician
- Followup template selection to impersonate this technical user and to welcome him/her
- Coming soon: Creation of calendars to use multiple technical users
- Coming soon: Set a fixed delay time for greeting messages
- Coming soon: Calendar-dependent welcome messages for each entity in GLPI

Currently only available with tickets.

## Installation

You must respect the name of the folder created in the plugins directory!

```sh
$ su -s /bin/bash apache 
$ cd ROOT_GLPI_DIR
$ wget https://github.com/miguelanruiz/engage/releases/download/v1.0.1/engage-1.0.1.tar.bz2
$ tar xvf engage-1.0.1.tar.bz2
$ rm engage-1.0.1.tar.bz2
```

After that go to the GLPI plugins (GLPI WEB Instacne) administration and continue the configuration process. Click Install and then Activate.

## Contributing

* Open a ticket for each bug/feature so it can be discussed
* Follow [development guidelines](http://glpi-developer-documentation.readthedocs.io/en/latest/plugins/index.html)
* Refer to [GitFlow](http://git-flow.readthedocs.io/) process for branching
* Work on a new branch on your own fork
* Open a PR that will be reviewed by a developer