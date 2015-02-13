CHANGELOG
=========

1.6.2
-----

Changes:

 * Temporary remove classic interface injection mechanism 

1.6.1
-----

Changes:

 * Symbol '@' use method 'get' of container

1.6.0
-----

Added functionality:

 * Create facade interface in ConfigCompile class
 * Create method of getting interface implementation: getInterface, hasInterface
 * Create interfaces aliases
 * Create universal methods: get, has. Return or check parameter, service, interface or tag.
 * Full support DI configuration in annotations
 * Annotation @autowired for automatical creted DI configuration
 * Autodetect service name if empty service annotation

1.5.5
-----

Changes:

 * Remove throw exception if undefined tag

1.5.4
-----

Changes:

 * Unlink file before builded configuration

1.5.3
-----

Changes:

 * Upgrade merge configurations algorithm with parent services
 * Upgrade file dumper algorithm

1.5.2
-----

Changes:

 * Update DiConfig builder

1.5.1
-----

Changes:

 * Fix composer configuration

1.5.0
-----

Changes:

 * Rename to 'Butterfly. PHP Configuration Component'
 * Rename 'SyringeBuilder' to 'DiConfig' 
 * Extract actions: parse and merge configuration to component 'butterfly-project/config' 

1.4.7
-----

Changes:

 * Fix bug for parse empty Yaml configs

1.4.6
-----

Changes:

 * Make it possible to use php 5.3

1.4.5
-----

Changes:

 * Fix bug for incorrect Builder DI configuraton

1.4.4
-----

Changes:

 * Fix bug for di-interfaces section

1.4.3
-----

Changes:

 * SyringeBuilder refactoring

1.4.2
-----

Changes:

 * Change build config logic

1.4.1
-----

Added functionality:

 * Added Json parser
 * Added parser tests 

1.4.0
-----

Added functionality:

 * ContainerConfigurationBuilder moved to SyringeBuilder with adapter uses.

1.3.2
-----

Changes:

 * Fix bug for orders of call postTrigger

1.3.1
-----

Added functionality:

 * Autobuild container configuration from php, yml files

1.3.0
-----

Changes:

 * Added IoC Builder

Added functionality:

 * Templates vars (Sf2 ParameterBag resolver)
 * Validation of Service configuration
 * Services aliases
 * Tag build
 * Configuration inheritance
 * ServiceVisitors load as list

1.2.2
-----

Changes:

 * Changet tag description syntax in IoC configuration

1.2.1
-----

Changes:

 * Fix composer configuration

1.2.0
-----

Added functionality:

 * Interface injection

Changes:

 * Configuration builder extract to SyringeBuilder project

1.1.0
-----

Added functionality:

 * Service aliases
 * Triggers (basic and static). Call methods before (pre) and after (post)
   service created.
 * Syntetic services
 * Dependency injection in private and protected object properties
 * Configuration inheritance

1.0.1
-----

Changes:

 * Preparing to deploy in Packagist.org

1.0.0
-----

Added functionality:

 * Check parameter availabilty
 * Get parameter
 * Check service availabilty
 * Dependency injection through constructor
 * Dependency injection through method
 * Dependency injection through public property
 * Dependency injection for tag
 * Get service
 * Get service through static factory
 * Get service through factory
 * Get service as singleton mode
 * Get service as new-instance mode
 * Get service as prototype mode
 * Get services list by tag
