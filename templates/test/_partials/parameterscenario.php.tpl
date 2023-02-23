       {% if ! {{ has_type }} || ! in_array('{{ type }}', ['string', 'int', 'float', 'bool'] ) : %}
              '{{ key }}' => null,
       {%  endif %}
       {% if {{ has_type }} && in_array('{{ type }}', ['int', 'float',] ) : %}
              '{{ key }}' => 0,
       {%  endif %}
       {% if {{ has_type }} && '{{ type }}' === 'string' : %}
              '{{ key }}' => '',
       {%  endif %}
       {% if {{ has_type }} && '{{ type }}' === 'bool' : %}
              '{{ key }}' => false,
       {%  endif %}