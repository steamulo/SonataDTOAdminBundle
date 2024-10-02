<?php

namespace Vtech\Bundle\SonataDTOAdminBundle\Datagrid;

use Sonata\AdminBundle\Datagrid\Pager as BasePager;

class Pager extends BasePager
{
    /**
     * {@inheritdoc}
     */
    public function init()
    {
        if (!$this->getQuery()) {
            throw new \RuntimeException('Uninitialized QueryBuilder');
        }

        $this->resetIterator();
        $this->setNbResults($this->computeNbResults());

        $query = $this->getQuery();

        if ($this->isExportMode()) {
            // Pour l'exportation, récupérez tous les résultats
            $this->setLastPage(0);
            $query->setFirstResult(null);
            $query->setMaxResults(null); // Retirez la limite de résultats
        } else {
            if (0 == $this->getPage() || 0 == $this->getMaxPerPage()) {
                $this->setLastPage(0);
                $query->setFirstResult(0);
                $query->setMaxResults(0);
            } else {
                $offset = ($this->getPage() - 1) * $this->getMaxPerPage();

                $query->setFirstResult($offset);
                $query->setMaxResults($this->getMaxPerPage());
                $this->initializeIterator();

                $this->setLastPage(ceil($this->getNbResults() / $this->getMaxPerPage()));
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getResults()
    {
        if ($this->results) {
            return $this->results;
        }

        $this->results = $this->getQuery()->execute([]);

        return $this->results;
    }

    /**
     * @return ProxyQuery
     */
    public function getQuery()
    {
        $query = parent::getQuery();
        if (null === $query) {
            return null;
        }

        if (!$query instanceof ProxyQuery) {
            throw new \RuntimeException(sprintf('Query must be instance of %s, %s given', ProxyQuery::class, get_class($query)));
        }

        return $query;
    }

    /**
     * @return int
     */
    private function computeNbResults()
    {
        return $this->getQuery()->getNbResults();
    }

    /**
     * Méthode pour vérifier si nous sommes en mode d'exportation
     * @return bool
     */
    private function isExportMode()
    {
        return isset($_REQUEST['format']) && in_array($_REQUEST['format'], ["csv", "json", "xls", "xml"]);
    }
}
