<?php
namespace Install\Lib;

//App::uses('ClassRegistry', 'Utility');
//App::uses('Model', 'Model');

use Cake\Filesystem\File;
use Cake\Log\Log;
use Cake\Core\Exception\Exception as CakeException;
use Cake\Utility\Hash;
use Cake\Core\App;
use Cake\Filesystem\Folder;

/**
 * Data Migration Utility class
 *
 * @package Croogo.Extensions.Lib.Utility
 */
class DataMigration
{

    /**
     * Load a single data file
     *
     * Options:
     *   `extract` - Path to identify an entry for `Hash::extract()`
     *
     * @param $file
     * @param array $options Options array
     * @return bool True if loading was successful
     * @throws CakeException
     * @throws UnexpectedException
     * @internal param string $path Path to directory containing data files
     */
    public function loadFile($file, $options)
    {
        if (!file_exists($file)) {
            throw new CakeException($file . ' not found');
        }
        if (!is_readable($file)) {
            throw new CakeException($file . ' not readable');
        }
        $dir = dirname($file);
        $pathinfo = pathinfo($file);
        $options = Hash::merge(array(
            'class' => $pathinfo['filename'],
        ), $options);
        return $this->load($dir, $options);
    }

    /**
     * Load data files
     *
     * Options:
     *   `class` - Class to load. Default to load all classes in directory
     *   `extract` - Path to identify an entry for `Hash::extract()`
     *
     * @param string $path Path to directory containing data files
     * @param array $options Options array
     * @return bool True if loading was successful
     * @throws CakeException
     * @throws UnexpectedException
     */
    public function load($path, $options = array())
    {
        if (!is_dir($path)) {
            throw new CakeException('Argument not a directory: ' . $path);
        }
        $options = Hash::merge(array(
            'ds' => 'default',
        ), $options);

        if (isset($options['class'])) {
            $dataObjects = array($options['class']);
        }
        $dir = new Folder($path);
        $files = $dir->find('.*\.php');

        foreach ($files as $file) {
            if (!class_exists($file)) {
                include($path . DS . $file);
            }
            $file = new File($dir->pwd() . DS . $file);
            debug($file);
//            $fileName = basename($file);
//            $objects[] = substr($fileName, 0, -4);
//            $classVars = get_class_vars($file);
//            $modelAlias = substr($file, 0, -4);
//            $table = $classVars['table'];
//            $records = $classVars['records'];
//            $uniqueKeys = null;
            if (!empty($options['extract'])) {
                $records = Hash::extract($records, $options['extract']);
            }
            $Model = new Model(array(
                'name'  => $modelAlias,
                'table' => $table,
                'ds'    => $options['ds'],
            ));

            if (!empty($classVars['uniqueFields'])) {
                $uniqueKeys = array_flip((array)$classVars['uniqueFields']);
                foreach ((array)$classVars['uniqueFields'] as $field) {
                    if (!$Model->hasField($classVars['uniqueFields'])) {
                        throw new UnexpectedException("$field is not found in table $table");
                    }
                }
            }

            if (is_array($records) && count($records) > 0) {
                $i = 0;
                foreach ($records as $record) {
                    if (isset($uniqueKeys)) {
                        $conditions = array_intersect_key($record, $uniqueKeys);
                        $count = $Model->find('count', compact('conditions'));
                        if ($count > 0) {
                            continue;
                        }
                    }
                    $Model->create($record);
                    $saved = $Model->save();
                    if (!$saved) {
                        CakeLog::error(sprintf(
                            'Error loading row #%s for table `%s`',
                            $i + 1,
                            $table
                        ));
                        return false;
                    }
                    $i++;
                }
                $Model->getDatasource()->resetSequence(
                    $Model->useTable, $Model->primaryKey
                );
            }
            ClassRegistry::removeObject($modelAlias);
        }
        return true;
    }

    /**
     * Generate data files
     *
     * The first two arguments will be passed to Model::find().
     * `$options` accepts the following keys:
     * - `model`: accepts `name`, `table`, and `ds`. See `ClassRegistry::init()`
     * - `output`: Path to output file
     *
     * @param string $type Type of query, eg: 'first' or 'all'. See Model::find()
     * @param array $query Query options passed as second argument to Model::find()
     * @param array $options Array of options. Accepts `model` and `output` keys
     * @see Model::find()
     * @return bool
     */
    public function generate($type, $query = array(), $options = array())
    {
        $options = Hash::merge(array(
            'model'  => array(
                'name'  => null,
                'table' => null,
                'ds'    => null,
            ),
            'output' => null,
        ), $options);

        $modelOptions = $options['model'];
        $name = $modelOptions['name'];
        $table = $modelOptions['table'];
        $ds = $modelOptions['ds'];

        $Model = new Model(array(
            'name'  => $name,
            'table' => $table,
            'ds'    => $ds,
        ));
        $ds = $Model->getDataSource();
        $records = $Model->find($type, $query);

        // generate file content
        $recordString = '';
        foreach ($records as $record) {
            $values = array();
            foreach ($record[$name] as $field => $value) {
                $value = $ds->value($value);
                $values[] = "\t\t\t'$field' => $value";
            }
            $recordString .= "\t\tarray(\n";
            $recordString .= implode(",\n", $values);
            $recordString .= "\n\t\t),\n";
        }
        $content = "<?php\n\n";
        $content .= "class " . $name . "Data" . " {\n\n";
        $content .= "\tpublic \$table = '" . $table . "';\n\n";
        $content .= "\tpublic \$records = array(\n";
        $content .= $recordString;
        $content .= "\t);\n\n";
        $content .= "}\n";

        return $this->_writeFile($options['output'], $content);
    }

    /**
     * Writes outputfile
     *
     * @param string $outputFile Output file name
     * @param string $content File content
     * @return boolean Success
     */
    protected function _writeFile($outputFile, $content)
    {
        $File = new File($outputFile, true);
        return $File->write($content);
    }

}
