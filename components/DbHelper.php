<?php
///////////////////////////////////////////////////////////////////////////////
//
// Licensed Source Code - Property of ProjectKit.net
//
// © Copyright ProjectKit.net 2015. All Rights Reserved.
//
///////////////////////////////////////////////////////////////////////////////
/**
 * The Database Helper class
 *
 * @author Bui Sy Nguyen <nguyenbs@gmail.com>
 */
class DbHelper {
    /**
     * Inserts a row into a table based on attributes.
     * @param string $table the table to insert
     * @param array $attributes list of attributes that need to be saved.
     * @return boolean whether the attributes are valid and the record is inserted successfully.
     */
    public static function insert($table, $attributes)
    {
        return 0 < self::db()->createCommand()->insert($table,$attributes);
    }

    /**
     * @return \yii\db\Connection
     */
    private static function db()
    {
        return Yii::$app->db;
    }

    /**
     * Batch-insert a list of data to a table.
     * This method could be used to achieve better performance during insertion of the large
     * amount of data into the database table.
     *
     * For example,
     *
     * ~~~
     * DbHelper::batchInsert('user', [
     *     ['name' => 'Tom', 'age' => 30],
     *     ['age' => 20, 'name' => 'Jane'],
     *     ['name' => 'Linda', 'age' => 25],
     * ]);
     * ~~~
     *
     * Note that the values in each row must match the corresponding column names.
     *
     * @param string $table the table that new rows will be inserted into.
     * @param array $data list data to be inserted, each value should be an array in format (column name=>column value).
     * If a key is not a valid column name, the corresponding value will be ignored.
     * @return integer number of rows affected by the execution.
     */
    public static function batchInsert($table, $data)
    {
        Yii::trace('insertMultiple()','application.DbHelper');
        $columns = [];
        $i = 0;

        foreach($data as $dataRow)
        {
            foreach($dataRow as $key=>$value)
            {
                if(!array_key_exists($columns, $key))
                {
                    $columns[$key] = $i;
                    $i++;
                }
            }
        }

        $rows = [];

        foreach($data as $dataRow)
        {
            $row = [];
            foreach($columns as $key=>$i)
            {
                $row[$i] = array_key_exists($dataRow, $key) ? $dataRow[$key] : null;
            }
        }
        return self::db()->createCommand()->batchInsert($table, array_keys($columns), $rows)->execute();
    }

    /**
     * Batch-update a list of data to a table.
     * This method could be used to achieve better performance during insertion of the large
     * amount of data into the database table.
     *
     * For example,
     *
     * ~~~
     * DbHelper::updateMultiple('user', [
     *     ['id' => 1, 'name' => 'Tom', 'age' => 30],
     *     ['id' => 2, 'age' => 20, 'name' => 'Jane'],
     *     ['id' => 3, 'name' => 'Linda', 'age' => 25],
     * ],
     * 'id');
     * ~~~
     *
     * Note that the values in each row must match the corresponding column names.
     *
     * @param string $table the table that has new rows will be updated.
     * @param array $data list data to be inserted, each value should be an array in format (column name=>column value).
     * If a key is not a valid column name, the corresponding value will be ignored.
     * @param mixed $pkNames Name or an array of names of primary key(s)
     * @return integer number of rows affected by the execution.
     */
    public static function updateMultiple($table, $data, $pkNames)
    {
        Yii::trace('updateMultiple()','application.DbHelper');
        $command = self::createMultipleUpdateCommand($table, $data, $pkNames);
        return $command->execute();
    }

    /**
     * Creates a multiple INSERT command with ON DUPLICATE KEY UPDATE statement.
     * This method compose the SQL expression via given part templates, providing ability to adjust
     * command for different SQL syntax.
     * @param string $table the table that has new rows will be updated.
     * @param array $data list data to be saved, each value should be an array in format (column name=>column value).
     * If a key is not a valid column name, the corresponding value will be ignored.
     * @param mixed $pkNames Name or an array of names of primary key(s)
     * @param array $templates templates for the SQL parts.
     * @throws \yii\db\Exception
     * @return \yii\db\Command multiple insert command
     */
    private static function createMultipleUpdateCommand($table, $data, $pkNames, array $templates=array())
    {
        $templates=array_merge(
            [
                'rowUpdateStatement'=>'UPDATE {{tableName}} SET {{columnNameValuePairs}} WHERE {{rowUpdateCondition}}',
                'columnAssignValue'=>'{{column}}={{value}}',
                'columnValueGlue'=>', ',
                'rowUpdateConditionExpression'=>'{{pkName}}={{pkValue}}',
                'rowUpdateConditionJoin'=>' AND ',
                'rowUpdateStatementGlue'=>'; ',
            ],
            $templates
        );

        $tableSchema=self::db()->schema->getTableSchema($tableName=$table);

        if($tableSchema===null)
            throw new \yii\db\Exception(Yii::t('yii','Table "{table}" does not exist.',
                ['{table}'=>$tableName]));
        $tableName=self::db()->quoteTableName($tableSchema->name);
        $params=[];
        $quoteColumnNames=[];

        $columns=[];

        foreach($data as $rowData)
        {
            foreach($rowData as $columnName=>$columnValue)
            {
                if(!in_array($columnName,$columns,true))
                    if($tableSchema->getColumn($columnName)!==null)
                        $columns[]=$columnName;
            }
        }

        foreach($columns as $name)
            $quoteColumnNames[$name]=self::db()->schema->quoteColumnName($name);

        $rowUpdateStatements=[];
        $pkToColumnName=[];

        foreach($data as $rowKey=>$rowData)
        {
            $columnNameValuePairs=[];
            foreach($rowData as $columnName=>$columnValue)
            {
                if(is_array($pkNames))
                {
                    foreach($pkNames as $pk)
                    {
                        if (strcasecmp($columnName, $pk) == 0)
                        {
                            $params[':'.$columnName.'_'.$rowKey] = $columnValue;
                            $pkToColumnName[$pk]=$columnName;
                            continue;
                        }
                    }
                }
                else if (strcasecmp($columnName, $pkNames) == 0)
                {
                    $params[':'.$columnName.'_'.$rowKey] = $columnValue;
                    $pkToColumnName[$pkNames]=$columnName;
                    continue;
                }
                /** @var \yii\db\ColumnSchema $column */
                $column=$tableSchema->getColumn($columnName);
                $paramValuePlaceHolder=':'.$columnName.'_'.$rowKey;
                $params[$paramValuePlaceHolder]=$column->dbTypecast($columnValue);

                $columnNameValuePairs[]=strtr($templates['columnAssignValue'],
                    [
                        '{{column}}'=>$quoteColumnNames[$columnName],
                        '{{value}}'=>$paramValuePlaceHolder,
                    ]);
            }

            //Skip all rows that don't have primary key value;
            if(is_array($pkNames))
            {
                $rowUpdateCondition = '';
                foreach($pkNames as $pk)
                {
                    if(!isset($pkToColumnName[$pk]))
                        continue;
                    if($rowUpdateCondition != '')
                        $rowUpdateCondition = $rowUpdateCondition.$templates['rowUpdateConditionJoin'];
                    $rowUpdateCondition = $rowUpdateCondition.strtr($templates['rowUpdateConditionExpression'], array(
                        '{{pkName}}'=>$pk,
                        '{{pkValue}}'=>':'.$pkToColumnName[$pk].'_'.$rowKey,
                    ));
                }
            }
            else
            {
                if(!isset($pkToColumnName[$pkNames]))
                    continue;
                $rowUpdateCondition = strtr($templates['rowUpdateConditionExpression'], array(
                    '{{pkName}}'=>$pkNames,
                    '{{pkValue}}'=>':'.$pkToColumnName[$pkNames].'_'.$rowKey,
                ));
            }

            $rowUpdateStatements[]=strtr($templates['rowUpdateStatement'],array(
                '{{tableName}}'=>$tableName,
                '{{columnNameValuePairs}}'=>implode($templates['columnValueGlue'],$columnNameValuePairs),
                '{{rowUpdateCondition}}'=>$rowUpdateCondition,
            ));
        }

        $sql=implode($templates['rowUpdateStatementGlue'], $rowUpdateStatements);

        //Must ensure Yii::$app->db->emulatePrepare is set to TRUE;
        $command=self::db()->createCommand($sql);

        foreach($params as $name=>$value)
            $command->bindValue($name,$value);

        return $command;
    }
}