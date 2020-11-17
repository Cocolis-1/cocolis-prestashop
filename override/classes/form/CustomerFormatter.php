<?php
use Symfony\Component\Translation\TranslatorInterface;

class CustomerFormatter extends CustomerFormatterCore
{
    private $translator;
    private $language;

    //translator and language are private in parent class, override need
    public function __construct(
        TranslatorInterface $translator,
        Language $language
    ) {
        $this->translator = $translator;
        $this->language = $language;
        
        parent::__construct($translator, $language);
    }
    
    public function getFormat()
    {
        $format = parent::getFormat();
      
        //override/add all customisations for fields
        $format['birthday'] = (new FormField())
        ->setName('birthday')
        ->setType('text')
        ->setLabel(
            $this->translator->trans(
            'Birthdate',
            [],
            'Shop.Forms.Labels'
        )
        )
        ->addAvailableValue('placeholder', Tools::getDateFormat())
        ->addAvailableValue(
            'comment',
            $this->translator->trans('(E.g.: %date_format%)', array('%date_format%' => Tools::formatDateStr('31 May 1970')), 'Shop.Forms.Help')
        )
        ->setRequired(true);

        //As addConstraints method is private we need to call the logic here. We don't need to iterate over all the fields again, just the changed ones.
        $constraints = Customer::$definition['fields'];
        $field = $format['birthday'];

        if (!empty($constraints[$field->getName()]['validate'])) {
            $field->addConstraint(
                $constraints[$field->getName()]['validate']
            );
        }

        return $format;
    }
}
