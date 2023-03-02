{{ properties }}
    /**
     * @var {{ main_class_type }}
     */
    protected ${{ main_class_name }};

    public function set_up() {
        parent::set_up();
{{ properties_initialisation }}
        $this->{{ main_class_name }} = new {{ main_class_type }}({{ init_params }});
    }