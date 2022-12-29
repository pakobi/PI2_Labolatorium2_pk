<?php

namespace SoapServer\Service;

use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\Sql\Sql;

class Movies
{
    public function __construct(public AdapterInterface $adapter, public array $config)
    {
    }

    /**
     * Fetches movies list
     *
     * @return array
     * @throws \SoapFault
     */
    public function fetchMovies(): array
    {
        try {
            $sql = new Sql($this->adapter);
            $select = $sql->select();
            $select->from(['m' => 'movies']);
            $select->order('m.rating');

            $sqlString = $sql->buildSqlString($select);
            $wynik = $this->adapter->query($sqlString, Adapter::QUERY_MODE_EXECUTE);

            return $wynik->toArray();
        } catch (\Exception $e) {
            throw new \SoapFault(500, $e->getMessage());
        }
    }

    /**
     * Adds a movie
     *
     * @param array $data
     * @return int
     */
    public function add(array $data): mixed
    {
        $sql = new Sql($this->adapter);
        $insert = $sql->insert('movies');
        $insert->values([
            'title' => $data['title'],
            'year' => $data['year'],
            'rating' => $data['rating'],
            'link' => $data['link'],
            'genre_id' => $data['genreId'],
        ]);
        $sqlString = $sql->buildSqlString($insert);
        $result = $this->adapter->query($sqlString, Adapter::QUERY_MODE_EXECUTE);

        return $result->getGeneratedValue();
    }


    /**
     * Get a movie
     * 
     * @param int $id
     * @return array
     */
    public function pobierz(int $id): array
    {
        $dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
        $select = $sql->select('movies');
        $select->where(['id' => $id]);
        $select->order('title');

        $selectString = $sql->buildSqlString($select);
        $wynik = $dbAdapter->query($selectString, $dbAdapter::QUERY_MODE_EXECUTE);

        //dd($id);

        if ($wynik->count()) {
            return $wynik->current()->getArrayCopy();
        } else {
            return [];
        }
    }

    /**
     * update a movie
     * 
     * @param int $id
     * @param array $data
     * @return void
     */
    public function aktualizuj(int $id, array $data)
    {
        $dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
        $update = $sql->update('movies');
        $update->set([
            'title' => $data['title'],
            'year' => $data['year'],
            'rating' => $data['rating'],
            'link' => $data['link'],
            'genre_id' => $data['genreId'],
        ]);
        $update->where(['id' => $id]);

        $selectString = $sql->buildSqlString($update);
        $dbAdapter->query($selectString, $dbAdapter::QUERY_MODE_EXECUTE);
    }

    
    /**
     * Delete a movie
     * 
     * @param int $id
     * @return void
     */
    public function usun(int $id): void
    {
        $dbAdapter = $this->adapter;

        $sql = new Sql($this->adapter);
        $deleteMovie = $sql->delete('movies');
        $deleteMovie->where(['id' => $id]);

        $sqlString = $sql->buildSqlString($deleteMovie);

        $dbAdapter->query($sqlString, $dbAdapter::QUERY_MODE_EXECUTE);
    }
}
