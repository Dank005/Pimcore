<?php



/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Enterprise License (PEL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PEL
 */

namespace App\Controller;

use Pimcore\Model\Asset;
use App\Form\CarSubmitFormType;
use App\Form\TechMapSubmitFormType;
use App\Website\Tool\Text;
use Pimcore\Model\DataObject\Service;
use Pimcore\Model\DataObject\Test;
use Pimcore\Model\DataObject\Fieldcollection;
use Pimcore\Model\DataObject\Fieldcollection\Data\Blocks;
use Pimcore\Model\DataObject\Operation2;
use Pimcore\Model\DataObject\Equipment;
use Pimcore\Model\DataObject\Content;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Length;
use Pimcore\Model\DataObject\TechMap;
use Pimcore\Model\DataObject\SymbolA;
use Pimcore\Model\DataObject\SymbolB;

class DefaultController extends BaseController
{
    /**
     * @Route("/examples", name="examples")
     *
     * @return Response
     */

    


    public function examplesAction(): Response
    {
        return $this->render('default/examples.html.twig');
    }

    /**
     *
     * @return array
     */
    #[Template('default/default.html.twig')]
    public function defaultAction(): array
    {
        return [];
    }

    public function genericMailAction(): Response
    {
        return $this->render('default/generic_mail.html.twig');
    }

    public function galleryRenderletAction(Request $request): Response
    {
        $params = [];
        if ($request->attributes->get('id') && $request->attributes->get('type') === 'asset') {
            $params['asset'] =  Asset::getById($request->attributes->getInt('id'));
        }

        return $this->render('default/gallery_renderlet.html.twig', $params);
    }
    

    #[Route('/default/createTechMap', name: 'create-TechMap')]
    public function createTechMapAction(Request $request): Response
    {  
        $form = $this->createForm(TechMapSubmitFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            $techMap = new TechMap();
            $techMap->setParent(Service::createFolderByPath('/upload/new'));
            $techMap->setKey(Text::toUrl($formData['name'] . '-' . time()));
            $techMap->setInput($formData['name']);

            $items = new Fieldcollection();
            $item = new Blocks();

            $symbolA = new SymbolA();
            $symbolA->setParent(Service::createFolderByPath('/upload/new'));
            $symbolA->setKey(Text::toUrl($formData['description'] . 'A-' . time()));

            $operation = new Operation2();
            $operation->setParent(Service::createFolderByPath('/upload/new'));
            $operation->setKey(Text::toUrl($formData['description'] . 'O-' . time()));
            $operation->setName($formData['operation']);

            $equipment = new Equipment();
            $equipment->setParent(Service::createFolderByPath('/upload/new'));
            $equipment->setKey(Text::toUrl($formData['description'] . 'E-' . time()));
            $equipment->setName($formData['equipment']);

            $content = new Content();
            $content->setParent(Service::createFolderByPath('/upload/new'));
            $content->setKey(Text::toUrl($formData['description'] . 'C-' . time()));
            $content->setContent($formData['content']);
            $content->save();

            $equipment->setContent($content);
            $equipment->save();

            //$operationEquip = Equipment::getById($formData['equipment']);
            $equipments = array($equipment);
            $operation->setEquipments($equipments);


            $operation->save();
            //$symbolAoperation = Operation2::getById($formData['operation']);

            $symbolA->setOperation2($operation);
            $symbolA->setName("NameSymbolA");

            $symbolA->save();
            //$symbolA = SymbolA::getById($formData['symbol']);
            //$symbolB = SymbolB::getById($formData['symbol']);
            //$symbolA = SymbolA::getById(1221); 

            $symbols = array($symbolA);
            
            $item->setSymbols($symbols);
            $item->setBlockName($formData['description']);

            $items->add($item);
            $techMap->setInfoBlocks($items);

            $techMap->save();

            return $this->render('default/test.html.twig', [
                'form' => $form->createView()
            ]);
        }

        //$testObjectsFolder = Service::createFolderByPath('/ProjectOperations');
        //$operations = $testObjectsFolder->getChildren();
        //$string = "Ababa";
        
        //return $this->render('default/test.html.twig', ['string' => $string, 'operations' => $operations]);
        return $this->render('default/test.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/default/edit", name="edit")
     *
     * @param Request $request
     *
     * @throws \Exception
     */
    public function editAction(Request $request)
    {
        $mapId = $request->request->get('mapId');
        $mapInput = $request->request->get('mapInput');
        $blockName = $request->request->get('blockName');

        $techMap = TechMap::getById(1223);
        $techMap->setInput($mapInput);
        $techMap->save();

        $techMapFolder = Service::createFolderByPath('/Product Data/TechMapData/TechMaps');
        $techMaps = $techMapFolder->getChildren();
        return $this->render('default/editMap.html.twig', ['techMaps' => $techMaps]);
    }

     /**
     * @Route("/default/handle_selector_change", name="handle_selector_change")
     *
     * @param Request $request
     *
     * @throws \Exception
     */
    public function handleSelectorChangeAction(Request $request)
    {
        // Получаем значение из запроса
        $selectedValue = $request->request->get('selectedValue');

        $selectedSymbol = $request->request->get('selectedSymbol1');
        
        $operationsFolder = Service::createFolderByPath('/Product Data/TechMapData/Operations');
        $operations = $operationsFolder->getChildren();

        $equipmentsFolder = Service::createFolderByPath('/Product Data/TechMapData/Equipments');
        $equipments = $equipmentsFolder->getChildren();

        $symbolsFolder = Service::createFolderByPath('/Product Data/TechMapData/Symbols');
        $symbols = $symbolsFolder->getChildren();

        if($selectedSymbol == 'A')
            return $this->render('default/test.html.twig', ['stroka' => $selectedSymbol, 'operations' => $operations,'equipments' => $equipments, 'symbols'  => $symbols ]);
        else
            return $this->render('default/test.html.twig', ['stroka' => $selectedSymbol, 'operations' => $equipments,'equipments' => $operations, 'symbols'  => $symbols ]);

        #return $this->redirect('/success'); // Перенаправление после обработки события
    }

    public function editMapAction(Request $request) //editMap
    {
        $techMapFolder = Service::createFolderByPath('/Product Data/TechMapData/TechMaps');
        $techMaps = $techMapFolder->getChildren();
        return $this->render('default/editMap.html.twig', ['techMaps' => $techMaps]);
    }

    public function testAction(Request $request) //createForm
    {  
        #$form = $this->createForm(TechMapSubmitFormType::class);
        #$form->handleRequest($request);
        #return $this->render('default/test.html.twig', [
        #    'form' => $form->createView()
        #]);

        $operationsFolder = Service::createFolderByPath('/Product Data/TechMapData/Operations');
        $operations = $operationsFolder->getChildren();

        $equipmentsFolder = Service::createFolderByPath('/Product Data/TechMapData/Equipments');
        $equipments = $equipmentsFolder->getChildren();

        $symbolsFolder = Service::createFolderByPath('/Product Data/TechMapData/Symbols');
        $symbols = $symbolsFolder->getChildren();

        return $this->render('default/test.html.twig', ['stroka' => 'No', 'operations' => $operations,'equipments' => $equipments, 'symbols'  => $symbols ]);
    }

    #[Route('/default/click', name: 'button-click')]
    public function buttonClickAction(Request $request): Response
    {
        $string = "Stroka";
        return $this->render('default/test.html.twig', ['string' => $string]);
    }

    #[Route('/default/createTest', name: 'create-test')]
    public function createTestAction(Request $request): Response
    {
        $form = $this->createForm(TechMapSubmitFormType::class);
        $form->handleRequest($request);

        $newTest = new Test(); 
        $newTest->setParent(Service::createFolderByPath('/upload/new'));
        $newTest->setKey(Text::toUrl('test-' . time()));
        $newTest->save();
        return $this->render('default/test.html.twig', ['form' => $form->createView(), 'string' => 'Создана модель тест', 'operations' => null]);
    }
    
}

