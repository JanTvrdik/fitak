<?php

/**
 * Description of SearchPresenter
 *
 * @author Vojtech Miksu <vojtech@miksu.cz>
 */
use Nette\Diagnostics\Debugger;

class SearchPresenter extends BasePresenter
{

    private $searchQuery, $allQuery, $includeComments, $vp, $tags;
    private $itemsCount = NULL;

    public function renderDefault($s, $comments )
    {
        $this->allQuery = $s;
        $this->includeComments = $comments;
        $this->template->s = $s;
        
        $parsed = $this->context->data->parseQuery($s);
        if (count($parsed) == 1){
            $this->searchQuery = $parsed[0];
            $this->tags = Array();
        }else{
            $this->searchQuery = $parsed[1];
            $this->tags = $parsed[0];
        }
        $this->template->tags = $this->tags;
        $this->template->itemsCount = $this->getItemsCount();

        // paginator...
        $this->vp = new VisualPaginator( $this, 'vp' );
        $paginator = $this->vp->getPaginator();
        $paginator->itemsPerPage = 15;
        $paginator->itemCount = $this->getItemsCount();
        
        if ($this->searchQuery != ""){
            $this->template->highlightKeywords = $this->context->data->getWordVariations( $this->searchQuery );
        }
        $this->template->data = $this->context->data->search( $this->searchQuery, $this->tags, $this->includeComments, $paginator->getLength(), $paginator->getOffset() );
    }
    
    public function renderTest( )
    {
        $a = $this->context->data->getTagsId(Array('zdm', 'pa1'));
        foreach ($a as $id){
            echo $id.", ";
        }
        print_r($a);
        exit;
    }

    protected function createComponentSearchForm()
    {
        $form = new SearchForm();
        $form->setDefaults( array(
            's' => $this->allQuery,
            'includeComments' => $this->includeComments
        ) );
        $form->onSuccess[] = callback( $form, 'submitted' );
        return $form;
    }

    protected function getItemsCount()
    {
        if( $this->itemsCount == NULL )
        {
            $this->itemsCount = $this->context->data->searchCount( $this->searchQuery, $this->tags, $this->includeComments );
        }
        return $this->itemsCount;
    }

}
