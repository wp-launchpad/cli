    public function set_up() {
        parent::set_up();
        {{ properties }}
        $this->{{ class_property }} = new {{ class_name }}({{ parameters }});
    }