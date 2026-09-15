const fs = require('fs');

const consultores = [
  { id: 'leandro', nombre: 'Leandro', rol: 'Conducción', disponibilidad: ['completa', 'hasta 15:50', 'completa', 'hasta 15:50', 'completa'], restricciones: 'No disponible martes y jueves después de las 15:50.', color: '#132B43' },
  { id: 'adria', nombre: 'Adria', rol: 'Senior', disponibilidad: ['7–14', '7–14', '7–14', '7–14', '7–14'], restricciones: 'Única senior con ventana para rutas rurales. Lleva la cartera de territorio.', color: '#2F7D5D' },
  { id: 'mariela', nombre: 'Mariela', rol: 'Senior', disponibilidad: ['7–13', '7–13', '7–13', '7–13', '7–13'], restricciones: 'Cartera urbana sin traslados. Cierre firme a las 13:00.', color: '#C4442E' },
  { id: 'francisco', nombre: 'Francisco', rol: 'Junior', disponibilidad: ['8–18', '8–18', '8–18', '8–18', 'libre'], restricciones: 'Facultad de lunes a jueves de 18:00 a 22:00. Fuera por FIT del 24 al 30 de septiembre.', color: '#4A7FA8' },
  { id: 'agustina', nombre: 'Agustina', rol: 'Junior', disponibilidad: ['8–18', '8–18', '8–18', '8–18', 'libre'], restricciones: 'Facultad de lunes a jueves de 18:00 a 22:00. Fuera por FIT del 24 al 30 de septiembre.', color: '#E8A33D' },
  { id: 'noelia', nombre: 'Noelia', rol: 'Junior', disponibilidad: ['8–18', '8–18', '8–18', '8–18', 'libre'], restricciones: 'Facultad de lunes a jueves de 18:00 a 22:00.', color: '#8FB8D4' },
  { id: 'cesia', nombre: 'Cesia', rol: 'Junior', disponibilidad: ['14–20', '14–20', '8–14', '14–20', '14–20'], restricciones: 'Única mañana libre: miércoles. Su fuerte son los toques de tarde.', color: '#6B3FA0' }
];

const proyectos = [
  {
    id: 'crova', app_id: 25, nombre: 'Los Crovas', titular: 'Carlos Javier Crova', linea: 'Raíz', puntaje: 4.22, celula: 1, sr: 'leandro', jr: 'cesia',
    diagnostico: [
      'Uno de los 4 o 5 piperos del país. Pieza en el Museo de la Pipa de Ámsterdam y en el Museo del Tabaco de Salta. Miembro de la Asociación de Piperos Interamericana y del Hobbit Pipe Club.',
      'Ya exporta a Francia, Estados Unidos e Italia, y genera turismo de cofradía espontáneo: le llegan clientes desde Santa Cruz, Tierra del Fuego y Neuquén.',
      'Taller, maquinaria e insumos propios. Viabilidad operativa inmediata, sin inversión previa.',
      'Producto con Sello Origen Chubut.',
      'El cuello no es el producto: es difusión y comercialización. No tiene tiempo ni recursos propios y depende de su hijo para el video.',
      'No busca masificar la venta. Quiere movimiento dentro de un nicho definido.'
    ],
    trabas: [
      'Camino de acceso al taller severamente deteriorado. Condiciona cualquier visita organizada.',
      'No disponible lunes ni miércoles por la mañana.',
      'Costo y logística de envío internacional, hoy artesanal.'
    ],
    ejes: [
      { t: 'Monetizar la visita al taller', items: ['Cuantificar el tiempo de producción que cede en cada visita.', 'Guion segmentado: cofrades y coleccionistas por un lado, turistas y familias por otro.', 'Precio por visita más esquema de escasez: de 3 a 5 días por pieza significa agenda acotada.'] },
      { t: 'Contenido y comunicación', items: ['Producción audiovisual 4K y dron con equipo de la Subsecretaría, vinculando la pipa al entorno patagónico.', 'Curaduría del canal de YouTube existente y de las redes.'] },
      { t: 'Vinculación institucional y comercial', items: ['Presencia con respaldo de Turismo en encuentros locales (Carao, Hotel Tehuelche, CIEFAP), no solo stand.', 'Circuito de regalo institucional y empresarial.', 'Gestión del camino de acceso ante el área que corresponda.'] }
    ],
    entregables: [
      'Precio de visita guiada fijado y comunicado.',
      'Guion de visita segmentado por público.',
      'Repositorio audiovisual 4K y dron.',
      'Al menos un vínculo institucional o comercial nuevo iniciado.'
    ]
  },
  {
    id: 'haiku', app_id: 32, nombre: 'Haiku Casa de Té', titular: 'Estrella Cerocchi', linea: 'Acelera', puntaje: 3.57, celula: 4, sr: 'mariela', jr: 'agustina',
    diagnostico: [
      'Puntuación muy alta en la selección, con coincidencia entre todos los evaluadores. Propuesta de valor consolidada.',
      'Cuatro años de trayectoria con interrupciones. Local propio abierto en junio de 2022.',
      'Trabaja sola. Historial de colaboradoras en redes y dificultad declarada para delegar.',
      'Ya hace registro manual de datos, sin sistematizar ni graficar.',
      'El cuello real: nunca logró generar clientes regulares ni comunidad. Se la percibe desde afuera como un lugar cerrado, aunque esté operando.'
    ],
    trabas: [
      'En algún momento del año pensó en cerrar. El encuadre es de sostén, no solo técnico: no forzar ritmo ni presión de entregables.',
      'Costeo de conservación en frío: los productos con crema duran dos días.',
      'Resistencia local a reservar. Hay que ajustar el mecanismo, no necesariamente eliminarlo.'
    ],
    ejes: [
      { t: 'Definir qué es Haiku', items: ['Resolver la disyuntiva entre alta gastronomía y espacio de encuentro. No conviven sin fricción.', 'Una vez definida, ordenar qué se sacrifica en función de esa jerarquía.'] },
      { t: 'Cápsulas de experiencia y recurrencia', items: ['Formalizar Té de amigas, Haiku al atardecer los viernes y una propuesta de sábados, con precio propio.', 'Menú fijo mensual, ya probado en julio con buen resultado, como base de la oferta accesible.'] },
      { t: 'Comunicación y comunidad', items: ['Jerarquía visual en las piezas: mensaje grande y claro primero, marca después.', 'Pedido activo de etiquetado a clientes en redes.', 'Sistema simple de registro diario para reemplazar la memoria por datos.'] }
    ],
    entregables: [
      'Definición escrita de qué es Haiku y jerarquía de lo que no se toca.',
      'Cápsulas con precio y calendario semanal.',
      'Sistema de registro diario en uso y primer indicador de clientes regulares.'
    ]
  },
  {
    id: 'tambo', app_id: 41, nombre: 'La casa del tambo', titular: 'Sofia Winter', linea: 'Raíz', puntaje: 3.24, celula: 2, sr: 'adria', jr: 'noelia',
    diagnostico: [
      'Dos hectáreas en la salida de Trevelin. Fue el primer frigorífico de la zona, fundado por su abuelo, luego conservas de la familia Garitano.',
      'Construcción colonial con sótano grande, abandonada casi 40 años y otros 10 sin uso bajo la propiedad de su padre.',
      'Ubicación estratégica junto al molino, los tulipanes, la cascada y La Floral. Zona antes alejada, hoy con barrios y loteos al frente.',
      'Profesora de equitación (Hípico Argentino, Los Pinos), estudió marketing y trabaja como freelancer en comunicación. Flexibilidad horaria.',
      'Prioridad propia: ordenar ideas y arrancar por los caballos, antes que aromáticas, tours, gallinas u ordeñe.'
    ],
    trabas: [
      'Su padre, socio inversor, ausente hasta fin de septiembre: las definiciones de inversión esperan su regreso.',
      'Viaja a Buenos Aires hasta el jueves 16 de septiembre.',
      'Habilitaciones municipales pendientes: servicios, calles y agrimensura por deslindes.',
      'La temporada fuerte es la de tulipanes en octubre. El turismo de invierno viene bajo.'
    ],
    ejes: [
      { t: 'Operaciones', items: ['Priorizar equitación como ingreso de corto plazo que financie la infraestructura mayor.', 'Armar la pista y regularizar servicios y permisos municipales para actividad ecuestre.', 'Construir de abajo hacia arriba: empezar con lo que hay, sin esperar la infraestructura ideal.'] },
      { t: 'Comercialización', items: ['Esquema económico-financiero simplificado para el diálogo con el socio inversor.', 'Paquetizar la actividad ecuestre en experiencia con guion, no como clase suelta.'] },
      { t: 'Comunicación', items: ['Portfolio de marketing y contenido, aprovechando su oficio de freelancer.', 'Discurso de macro destino: integrar a Esquel en la narrativa aunque el predio esté en Trevelin.'] }
    ],
    entregables: [
      'Documentación del proyecto y portfolio enviados (comprometido para el 16/9).',
      'Pista de equitación operativa con turnos definidos y permisos en trámite.',
      'Esquema económico-financiero para uso interno con el socio inversor.',
      'Al menos una experiencia ecuestre paquetizada, con guion y precio.'
    ]
  },
  {
    id: 'flypark', app_id: 12, nombre: 'FLYPARK', titular: 'FABRICIO GUGLIELMETTI', linea: 'Acelera', puntaje: 4.22, celula: 4, sr: 'leandro', jr: 'agustina',
    diagnostico: ['Parque aéreo y aventura en árboles con tirolesas. Pionero en La Hoya en 2006; ahora relanzamiento en predio accesible.', 'Excelente perfil de emprendedor y experiencia técnica en seguridad y montaña.', 'Alta demanda de turismo familiar y activo.'],
    trabas: ['Permisos y habilitaciones de seguridad de altura.', 'Inversión en cableado, arneses y plataformas.', 'Convenio de cesión o alquiler del predio.'],
    ejes: [{ t: 'Habilitaciones y Seguridad', items: ['Protocolo de seguridad bajo normas IRAM.', 'Trámite municipal y seguro de responsabilidad civil.'] }, { t: 'Modelo Comercial', items: ['Tarifario por circuito y pases familiares.', 'Convenios con escuelas y agencias receptivas.'] }],
    entregables: ['Circuito y plano técnico aprobado.', 'Tarifario comercial cerrado y ficha para agencias.']
  },
  {
    id: 'porota', app_id: 19, nombre: 'LO DE POROTA... "meriendas de campo y arqueología familiar patagónica"', titular: 'Alda Inés Mateo Garin', linea: 'Acelera', puntaje: 3.85, celula: 3, sr: 'adria', jr: 'francisco',
    diagnostico: ['Meriendas campestres y relato histórico de pioneros en predio rural con encanto.', 'Fuerte valor patrimonial y arqueología familiar patagónica.', 'Gran calidez de anfitriona.'],
    trabas: ['Acondicionamiento de baños y salón de té para invierno/lluvia.', 'Definición de cupo máximo por turno para preservar la intimidad.'],
    ejes: [{ t: 'Guion Interpretativo', items: ['Curaduría de objetos familiares y relatos guiados.', 'Estructura de la merienda en 3 pasos sensoriales.'] }, { t: 'Comercialización', items: ['Sistema de reservas previas por WhatsApp.', 'Alianza con hoteles boutique para derivación.'] }],
    entregables: ['Guion de la merienda escrito.', 'Carta de menú patagónico y tarifario por persona.']
  },
  {
    id: 'senderos', app_id: 31, nombre: 'Senderos con Identidad', titular: 'Gabriela Verónica Farias y Florencia Micaela Couget', linea: 'Acelera', puntaje: 3.33, celula: 3, sr: 'adria', jr: 'francisco',
    diagnostico: ['Salidas guiadas de senderismo con reconocimiento de flora, fauna nativa y patrimonio.', 'Equipo con formación técnica y habilitación de guías de montaña.', 'Propuesta alineada al turismo de bajo impacto ambiental.'],
    trabas: ['Falta de salidas regulares con días fijos asegurados.', 'Dependencia de demanda espontánea.'],
    ejes: [{ t: 'Calendario Regular', items: ['Fijar 3 salidas semanales garantizadas.', 'Definir punto de encuentro céntrico accesible.'] }, { t: 'Canal Hotelero', items: ['Folleto en recepciones con código QR directo.', 'Comisión clara para recepcionistas.'] }],
    entregables: ['Calendario de salidas semanales confirmado.', 'Ficha técnica para agencias y hoteles de Esquel.']
  },
  {
    id: 'yamamori', app_id: 22, nombre: 'Yamamori Travel', titular: 'María Julia Montero', linea: 'Acelera', puntaje: 3.33, celula: 4, sr: 'mariela', jr: 'agustina',
    diagnostico: ['Agencia de viajes de nicho orientada a turismo consciente, baños de bosque y bienestar emocional.', 'Contactos comerciales y mercado emisivo en Buenos Aires y Córdoba.', 'Propuesta para grupos reducidos y viajeros de alto valor.'],
    trabas: ['Encuadre legal (legajo EVyT propio o alianza con receptivo habilitado).', 'Tarifarios netos con prestadores de la comarca.'],
    ejes: [{ t: 'Alianzas Receptivas', items: ['Acuerdo con agencia receptiva local para emisión y seguros.', 'Contratos con prestadores locales.'] }, { t: 'Paquetes Estrella', items: ['Estandarizar 2 salidas fijas para la temporada.', 'Venta previa a comunidades de bienestar.'] }],
    entregables: ['Convenio comercial con agencia receptiva.', 'Dossier comercial de los 2 paquetes cerrados.']
  },
  {
    id: 'arroyo', app_id: 15, nombre: 'EL ARROYO QUE NOS VE CRECER...', titular: 'MARCELO TROIANO', linea: 'Acelera', puntaje: 3.32, celula: 3, sr: 'adria', jr: 'francisco',
    diagnostico: ['Experiencia interpretativa en torno a la cuenca hídrica y bosque nativo.', 'Foco en educación ambiental, turismo regenerativo y avistaje.', 'Emprendedor con gran conocimiento local.'],
    trabas: ['Señalética interpretativa en el sendero.', 'Armado de paquete comercializable para visitantes generales.'],
    ejes: [{ t: 'Puesta en Valor del Sendero', items: ['Infografía de paradas clave y especies nativas.', 'Adecuación de huella y descansos.'] }, { t: 'Comercialización', items: ['Salidas combinadas con degustación local.', 'Convenios con escuelas y contingentes.'] }],
    entregables: ['Guion interpretativo de las estaciones del sendero.', 'Folletería digital y tarifario por visitante.']
  },
  {
    id: 'vicotita', app_id: 45, nombre: 'Merchandising turístico Vico+Tita', titular: 'María Eugenia Gutiérrez', linea: 'Acelera', puntaje: 3.27, celula: 1, sr: 'mariela', jr: 'cesia',
    diagnostico: ['Diseño y confección de recuerdos turísticos de alta calidad estética con identidad patagónica.', 'Capacidad de producción y taller propio.', 'Excelente alternativa al souvenir tradicional genérico.'],
    trabas: ['Distribución y puntos de venta fijos en la ciudad.', 'Financiamiento para compra de materia prima por mayor.'],
    ejes: [{ t: 'Puntos de Venta', items: ['Convenios de exhibidores en hoteles y chocolaterías.', 'Condiciones de venta firme y consignación.'] }, { t: 'Línea Exclusiva Esquel LAB', items: ['Colección temática de souvenirs identitarios.', 'Packaging ecológico con relato del destino.'] }],
    entregables: ['Catálogo de productos y lista de precios mayorista.', 'Al menos 3 puntos de venta cerrados en Esquel.']
  },
  {
    id: 'carpint', app_id: 33, nombre: 'Carpintero Esquel', titular: 'José Maximiliano Manquin', linea: 'Acelera', puntaje: 3.25, celula: 1, sr: 'mariela', jr: 'cesia',
    diagnostico: ['Taller de carpintería y artesanía en maderas nobles de la región.', 'Mano de obra calificada y diseño de objetos utilitarios y decorativos para turistas.', 'Disponibilidad de taller propio.'],
    trabas: ['Tiempos de producción artesanal vs volumen requerido.', 'Falta de espacio de exhibición propio céntrico.'],
    ejes: [{ t: 'Línea Turística', items: ['Seleccionar 4 productos estrella de fácil transporte en valija.', 'Grabado de identidad y sello Esquel.'] }, { t: 'Canales Comerciales', items: ['Inserción en tiendas de regionales y hoteles.', 'Venta por encargo y catálogo digital.'] }],
    entregables: ['Ficha de costos y precios de los 4 productos.', 'Convenio de exhibición en al menos 2 comercios.']
  },
  {
    id: 'corcho', app_id: 14, nombre: 'Corchobike', titular: 'Nestor andres colinecul', linea: 'Acelera', puntaje: 3.13, celula: 4, sr: 'leandro', jr: 'agustina',
    diagnostico: ['Taller de ciclismo, salidas guiadas urbanas y periféricas, clínicas para niños y guardería.', 'Gran conocimiento de los circuitos de pedaleo de Esquel y La Zeta.', 'Emprendedor con fuerte arraigo comunitario.'],
    trabas: ['Flota de bicicletas en óptimas condiciones para alquiler.', 'Habilitación de guardería y seguros de actividad.'],
    ejes: [{ t: 'Circuitos Urbanos Guiados', items: ['Diseño de 2 circuitos accesibles para familias.', 'Tarifario de guiada + alquiler de bici + casco.'] }, { t: 'Servicio al Alojamiento', items: ['Entrega y retiro de bicicletas en hoteles y cabañas.', 'Cartelería de guardería en eventos.'] }],
    entregables: ['Mapa y ficha de los circuitos autoguiados y guiados.', 'Tarifario de alquiler y salidas confirmado.']
  },
  {
    id: 'retro', app_id: 8, nombre: 'Patagonia Retro', titular: 'Paula Salvo', linea: 'Acelera', puntaje: 3.12, celula: 1, sr: 'mariela', jr: 'cesia',
    diagnostico: ['Colección nostálgica de más de 7 años de objetos cotidianos e históricos de Esquel.', 'Espacio físico en complejo de alojamiento.', 'Gran potencial para museo vivencial y tienda vintage.'],
    trabas: ['Separación física del acceso para no invadir la privacidad de las cabañas.', 'Guion museográfico y cédulas explicativas.'],
    ejes: [{ t: 'Apertura al Público', items: ['Diseño de recorrido y señalética independiente.', 'Horarios fijos de visita para tardes de lluvia/descanso.'] }, { t: 'Tienda Vintage', items: ['Selección de antigüedades y réplicas a la venta.', 'Entrada arancelada con consumición o recuerdo.'] }],
    entregables: ['Guion museográfico de las salas temáticas.', 'Entrada y horarios fijados y comunicados a Turismo.']
  },
  {
    id: 'margher', app_id: 3, nombre: 'Establecimiento Margherita', titular: 'Dante Oliva', linea: 'Raíz', puntaje: 4.42, celula: 2, sr: 'adria', jr: 'noelia',
    diagnostico: ['Chacra productiva y agroturismo de primer nivel.', 'Puntaje más alto de la línea Raíz (4.42).', 'Producción de alimentos de calidad y experiencia vivencial del campo cordillerano.'],
    trabas: ['Logística de traslados y camino rural en días de lluvia.', 'Calendarización de visitas regulares sin entorpecer la producción.'],
    ejes: [{ t: 'Recorrido Agroturístico', items: ['Guion de visita a las parcelas productivas.', 'Degustación de productos de chacra al cierre.'] }, { t: 'Comercialización Directa', items: ['Paseo con compra en origen.', 'Venta corporativa e institucional.'] }],
    entregables: ['Dossier de la visita agroturística empaquetada.', 'Tarifario para agencias y particulares.']
  },
  {
    id: 'lucero', app_id: 18, nombre: 'Centro Integral Ecuestre Lucero', titular: 'María Soledad Arrechea', linea: 'Raíz', puntaje: 4.02, celula: 2, sr: 'adria', jr: 'noelia',
    diagnostico: ['Cabalgatas terapéuticas, equinoterapia y turismo ecuestre familiar en entorno rural cuidado.', 'Puntaje destacado (4.02) con respaldo unánime del jurado.', 'Infraestructura ecuestre y caballos mansos.'],
    trabas: ['Seguro de actividad ecuestre turística y deslindes de responsabilidad.', 'Armado de paquetes para no iniciados.'],
    ejes: [{ t: 'Experiencias Ecuestres', items: ['Paseo suave de contacto y cepillado para niños.', 'Cabalgata al atardecer con merienda de campo.'] }, { t: 'Canal de Comercialización', items: ['Alianzas con centros de salud y alojamientos.', 'Venta por turnos programados.'] }],
    entregables: ['Guion y protocolo de seguridad de cabalgata.', 'Ficha comercial con precios y duración.']
  },
  {
    id: 'sabor', app_id: 7, nombre: 'Sabor Mapuche', titular: 'Lauriano Jose Rios', linea: 'Raíz', puntaje: 3.74, celula: 3, sr: 'adria', jr: 'francisco',
    diagnostico: ['Gastronomía ancestral de comunidades originarias, cocina de territorio y curanto.', 'Gran potencia cultural, identidad e interés para turismo internacional y nacional.', 'Cocineros y anfitriones reconocidos.'],
    trabas: ['Regularización bromatológica del espacio de elaboración.', 'Continuidad de fechas regulares frente a la estacionalidad.'],
    ejes: [{ t: 'Eventos Gastronómicos Fijos', items: ['Establecer fechas de curanto y fuegos quincenales.', 'Sistema de venta de cubiertos anticipados.'] }, { t: 'Relato y Cultura', items: ['Integrar la cosmovisión y el significado de los alimentos.', 'Souvenir gastronómico (especias y conservas).'] }],
    entregables: ['Calendario de banquetes ancestrales con reserva.', 'Ficha técnica para operadores de turismo receptivo.']
  },
  {
    id: 'laberin', app_id: 44, nombre: 'El secreto del laberinto', titular: 'Marta isabel San Martin', linea: 'Raíz', puntaje: 3.63, celula: 2, sr: 'adria', jr: 'noelia',
    diagnostico: ['Laberinto vegetal, senderos de chacra y entorno natural.', 'Atractivo visual de alto impacto para fotografía y turismo familiar.', 'Chacra consolidada en la comarca.'],
    trabas: ['Extensión de temporada más allá de los meses pico de verano.', 'Servicios complementarios de merienda y sanitarios.'],
    ejes: [{ t: 'Desestacionalización', items: ['Juegos temáticos de búsqueda para otoño y primavera.', 'Noches de luna llena en el laberinto.'] }, { t: 'Oferta Gastronómica', items: ['Té al paso y productos de la comarca.', 'Tienda de recuerdos de la chacra.'] }],
    entregables: ['Programa de actividades de media estación.', 'Tarifario y ficha comercial para agencias.']
  },
  {
    id: 'nire', app_id: 23, nombre: 'Pausa de ñire', titular: 'WALTER CERDÁ', linea: 'Raíz', puntaje: 3.32, celula: 2, sr: 'adria', jr: 'noelia',
    diagnostico: ['Complejo de cabañas inmerso en bosque nativo de ñires con senderos de contemplación y astroturismo.', 'Propuesta de desconexión y silencio en contacto con la montaña.', 'Emprendedor comprometido.'],
    trabas: ['Integrar actividades diurnas para el huésped que busca experiencias guiadas.', 'Promoción digital enfocada en público corporativo y parejas.'],
    ejes: [{ t: 'Cápsulas de Naturaleza', items: ['Sendero interpretativo del bosque nativo.', 'Noches de fogón y observación de estrellas.'] }, { t: 'Comercialización', items: ['Paquetes de fin de semana temáticos.', 'Convenios con guías de aventura locales.'] }],
    entregables: ['Guion de la experiencia de astroturismo y bosque.', 'Tarifario de experiencias opcionales para huéspedes.']
  },
  {
    id: 'truepat', app_id: 13, nombre: 'TRUE PATAGONIA', titular: 'Cecilia Fernanda Simonini', linea: 'Raíz', puntaje: 3.25, celula: 4, sr: 'mariela', jr: 'agustina',
    diagnostico: ['Paquete turístico receptivo boutique que integra naturaleza, gastronomía y patrimonio de la comarca.', 'Visión profesional del turismo receptivo y altos estándares de calidad.', 'Capacidad de articulación con prestadores.'],
    trabas: ['Cierre de acuerdos comerciales con tarifas netas confidenciales.', 'Canales de distribución emisivos en grandes ciudades.'],
    ejes: [{ t: 'Estandarización del Paquete', items: ['Itinerario cerrado de 4 días / 3 noches.', 'Costeo integral con margen de agencia garantizado.'] }, { t: 'Venta B2B', items: ['Presentación a agencias emisivas de Buenos Aires y Rosario.', 'Material promocional de alta calidad visual.'] }],
    entregables: ['Tarifario B2B confidencial y condiciones de contratación.', 'Brochure comercial y ficha técnica completa.']
  }
];

console.log('Proyectos count:', proyectos.length);
fs.writeFileSync('scripts/proyectos_data.json', JSON.stringify({ consultores, proyectos }, null, 2));
