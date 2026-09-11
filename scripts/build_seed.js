const fs = require('fs');
const path = require('path');

const { consultores, proyectos } = JSON.parse(fs.readFileSync(path.join(__dirname, 'proyectos_data.json'), 'utf8'));

const exactMeetings = {
  crova: [
    { num: 1, f: '2026-09-14', t: 'ind', lugar: 'Taller', hora: '10:00 a 11:30', titulo: 'Diagnóstico en terreno y relevamiento de taller', asistentes: ['leandro', 'cesia'],
      guia: 'Relevar el espacio físico de trabajo, la maquinaria existente y el estado del camino de acceso. Identificar dónde se puede ubicar un rincón de showroom sin entorpecer la producción.',
      preguntas: ['¿Cuántas horas reales de producción te quita recibir una visita de 45 minutos?', '¿Qué piezas tenés terminadas listas para exhibición hoy?', '¿Qué reclamos o dificultades te manifestaron los clientes que ya vinieron por el camino?'],
      objetivos: ['Relevar medidas y layout del taller para el sector de visita.', 'Evaluar el estado del camino y puntos críticos de acceso.', 'Establecer los días y horarios intocables de producción.'],
      check: ['Fotografiar el estado del camino y puntos de acceso', 'Medir el espacio disponible para showroom', 'Inventariar maquinaria y ubicar puntos de riesgo por polvo', 'Definir días de visita (descartar lunes y miércoles mañana)']
    },
    { num: 2, f: '2026-09-21', t: 'ind', lugar: 'Turismo', hora: '11:00 a 12:00', titulo: 'Eje comunicación y contenidos', asistentes: ['leandro', 'cesia'],
      guia: 'Analizar el material existente de video y redes. Definir una línea editorial que conecte la artesanía del pipero con el misticismo patagónico.',
      preguntas: ['¿Quién te ayuda hoy a filmar y editar?', '¿Qué repercusión tienen los videos en YouTube y de dónde te escriben?'],
      objetivos: ['Inventariar el material audiovisual propio.', 'Definir la línea editorial y el tono de la marca.'],
      check: ['Revisar el canal de YouTube', 'Inventariar el material audiovisual propio', 'Definir línea editorial', 'Acordar frecuencia de publicación']
    },
    { num: 3, f: '2026-09-24', t: 'ind', lugar: 'Taller', hora: '10:00 a 11:30', titulo: 'Guion de visita y cuantificación de tiempos', asistentes: ['leandro'],
      guia: 'Segmentar la experiencia entre cofrades/coleccionistas y turistas familiares. Calcular el costo de oportunidad del artesano.',
      preguntas: ['¿Qué espera ver un coleccionista experto vs una familia turista?', '¿Cuánto vale tu hora de trabajo de torno y lijado?'],
      objetivos: ['Estructurar dos versiones del guion de visita.', 'Fijar el valor de cesión de tiempo de producción.'],
      check: ['Cronometrar una visita tipo', 'Redactar el guion para cofrades', 'Redactar el guion para turistas y familias', 'Calcular las horas de producción que cede por visita']
    },
    { num: 4, f: '2026-09-30', t: 'gru', lugar: 'Célula 1 (Turismo)', hora: '09:30 a 12:00', titulo: 'Costeo, escasez y consignación (Célula 1)', asistentes: ['mariela', 'cesia'],
      guia: 'Encuentro grupal de la Célula 1 (Oficio y pieza). Poner en común criterios de fijación de precios, esquemas de escasez y condiciones de consignación.',
      preguntas: ['¿Cómo trasladamos el costo hora-hombre al precio de la experiencia?', '¿Qué comisión es justa dejarle al intermediario o comercio?'],
      objetivos: ['Comparar matrices de costeo entre proyectos de oficio.', 'Definir tope mensual de visitas.'],
      check: ['Llevar la planilla de costeo completa', 'Comparar con Carpintero, Vico+Tita y Patagonia Retro', 'Definir tope de visitas mensuales']
    },
    { num: 5, f: '2026-10-05', t: 'ind', lugar: 'Taller', hora: '09:00 a 12:00', titulo: 'Producción audiovisual en taller', asistentes: ['leandro', 'cesia'],
      guia: 'Sesión de rodaje profesional con equipo audiovisual y dron de la Subsecretaría para generar el banco de contenidos oficial.',
      preguntas: ['¿Qué fases del proceso de torneado son las más fotogénicas y llamativas?'],
      objetivos: ['Grabar tomas 4K de detalle y tomas aéreas del entorno.', 'Generar clips para reels y web.'],
      check: ['Agendar equipo 4K y dron', 'Armar el guion de tomas previas', 'Firmar autorización de uso de imagen', 'Hacer backup del material el mismo día']
    },
    { num: 6, f: '2026-10-07', t: 'ind', lugar: 'Turismo', hora: '11:00 a 12:00', titulo: 'Vinculación institucional y comercial', asistentes: ['leandro', 'cesia'],
      guia: 'Articular alianzas institucionales y comerciales con eventos gastronómicos y hoteles de alta gama de Esquel.',
      preguntas: ['¿En qué eventos de la ciudad te interesa tener presencia institucional?', '¿Qué formato de caja de regalo corporativo podemos armar?'],
      objetivos: ['Listar eventos y ferias del trimestre.', 'Presentar nota formal para la mejora del camino.'],
      check: ['Listar eventos locales del trimestre', 'Redactar carta de presentación institucional', 'Armar propuesta de regalo corporativo', 'Elevar la gestión del camino al área municipal correspondiente']
    },
    { num: 7, f: '2026-10-15', t: 'ind', lugar: 'Taller', hora: '10:00 a 11:30', titulo: 'Precio de visita y esquema de escasez cerrado', asistentes: ['leandro', 'cesia'],
      guia: 'Fijar el precio final de la visita y los días estrictos de atención.',
      preguntas: ['¿Confirmamos el precio en pesos y en dólares para extranjeros?', '¿Cómo se cobrará la reserva anticipada?'],
      objetivos: ['Cerrar política de tarifas y cupos.'],
      check: ['Fijar el precio de la visita guiada', 'Definir cupo máximo por turno', 'Confirmar calendario de días habilitados']
    },
    { num: 8, f: '2026-10-19', t: 'ind', lugar: 'Turismo', hora: '11:00 a 12:00', titulo: 'Ficha comercial y canales receptivos', asistentes: ['leandro', 'cesia'],
      guia: 'Redactar la ficha comercial para agencias receptivas premium y cofradías de fumadores internacionales.',
      preguntas: ['¿Qué porcentaje de comisión aceptamos para agencias de viaje?'],
      objetivos: ['Tener la ficha técnica lista para distribución.'],
      check: ['Redactar la ficha para receptivos', 'Redactar la ficha para cofradía internacional', 'Definir política de cancelación']
    },
    { num: 9, f: '2026-10-21', t: 'gru', lugar: 'Célula 1 (Turismo)', hora: '09:30 a 12:00', titulo: 'Puesta en común Célula 1 - Ronda 2', asistentes: ['mariela', 'cesia'],
      guia: 'Evaluación cruzada de los primeros pilotos y ajustes entre proyectos de artesanía y diseño.',
      preguntas: ['¿Qué feedback dieron los primeros visitantes de prueba?'],
      objetivos: ['Ajustar detalles del guion y recepción.'],
      check: ['Traer el resultado del piloto', 'Comparar precios entre los cuatro proyectos de la célula']
    },
    { num: 10, f: '2026-10-26', t: 'ind', lugar: 'Taller', hora: '10:00 a 11:30', titulo: 'Ficha técnica y tarifario final', asistentes: ['leandro', 'cesia'],
      guia: 'Revisión final de tarifas, acuerdos y piezas gráficas terminadas.',
      preguntas: ['¿Está todo listo para la rueda de negocios del cierre?'],
      objetivos: ['Cerrar carpetas comerciales.'],
      check: ['Cerrar la ficha técnica definitiva', 'Cerrar tarifario con comisiones', 'Revisar material audiovisual editado']
    },
    { num: 11, f: '2026-11-04', t: 'ind', lugar: 'Turismo', hora: '11:00 a 12:00', titulo: 'Preparación de la rueda de negocios', asistentes: ['leandro', 'cesia'],
      guia: 'Entrenar el pitch de 5 minutos y preparar las piezas a exhibir ante empresarios y agencias.',
      preguntas: ['¿Con qué 3 empresas específicas querés sentarte a negociar en la rueda?'],
      objetivos: ['Simular el pitch y seleccionar piezas de muestra.'],
      check: ['Preparar la presentación de 5 minutos', 'Elegir las empresas objetivo de la rueda', 'Llevar piezas para exhibir']
    },
    { num: 12, f: '2026-11-10', t: 'cie', lugar: 'Acto de cierre', hora: '10:00 a 13:00', titulo: 'Distinción, resultados y rueda de negocios', asistentes: ['leandro', 'adria', 'mariela', 'francisco', 'agustina', 'cesia', 'noelia'],
      guia: 'Acto plenario final de la 1ª cohorte de Esquel LAB. Entrega de certificados, exposición de resultados y vinculación comercial.',
      preguntas: ['¿Cuál es el próximo paso de tu proyecto para 2027?'],
      objetivos: ['Cerrar al menos un acuerdo comercial formal.'],
      check: ['Confirmar asistencia', 'Llevar material de exhibición', 'Cerrar al menos una reunión con una empresa local']
    }
  ],
  haiku: [
    { num: 1, f: '2026-09-10', t: 'ind', lugar: 'Local', hora: '09:30 a 11:00', titulo: 'Definir qué es Haiku: identidad y prioridades', asistentes: ['mariela', 'agustina'],
      guia: 'Abordar la disyuntiva entre alta gastronomía y espacio de encuentro comunitario. Enfoque de sostén emocional y claridad estratégica.',
      preguntas: ['¿Qué es lo innegociable de Haiku que no querés perder?', '¿Dónde sentís que se te va la energía en el día a día?'],
      objetivos: ['Escribir la definición medular de Haiku.', 'Listar qué aspectos de la operación se simplifican.'],
      check: ['Escribir en una frase qué es Haiku', 'Listar lo que no se toca', 'Listar lo que sí se puede ceder o simplificar']
    },
    { num: 2, f: '2026-09-16', t: 'ind', lugar: 'Local', hora: '09:30 a 11:00', titulo: 'Cápsulas de experiencia y recurrencia', asistentes: ['mariela', 'agustina'],
      guia: 'Estructurar productos empaquetados con precio propio (Té de amigas, Haiku al atardecer los viernes, propuesta de sábados).',
      preguntas: ['¿Cuánto cuesta hoy una merienda completa y qué margen real te deja?', '¿Qué día de la semana tenés el salón más vacío?'],
      objetivos: ['Nombrar y costear 3 cápsulas semanales.'],
      check: ['Nombrar cada cápsula', 'Fijar precio propio de cada una', 'Definir día y horario fijo', 'Definir cupo mínimo y máximo']
    },
    { num: 3, f: '2026-09-23', t: 'ind', lugar: 'Local', hora: '09:30 a 11:00', titulo: 'Conservación en frío y cálculo de mermas', asistentes: ['mariela', 'agustina'],
      guia: 'Resolver el cuello de botella de productos con crema que vencen a los 2 días. Diseñar línea de pastelería congelable de alta calidad.',
      preguntas: ['¿Cuánta pastelería descartás por semana?', '¿Qué masas y bases admiten congelado sin alterar textura?'],
      objetivos: ['Reducir el desperdicio en un 50% mediante planificación de horneado.'],
      check: ['Medir la merma de una semana completa', 'Identificar qué productos admiten congelado', 'Costear la conservación en frío']
    },
    { num: 4, f: '2026-09-30', t: 'gru', lugar: 'Célula 4 (Turismo)', hora: '09:30 a 12:00', titulo: 'Precio y comunicación cruzada (Célula 4)', asistentes: ['mariela'],
      guia: 'Puesta en común con proyectos de canal y volumen (Flypark, Yamamori, Corchobike, True Patagonia).',
      preguntas: ['¿Cómo comunicamos que Haiku está abierto y cómo eliminamos la fricción de la reserva?'],
      objetivos: ['Compartir estrategias de precios de recurrencia.'],
      check: ['Llevar el precio de las cápsulas', 'Traer una pieza de comunicación para criticar en grupo']
    },
    { num: 5, f: '2026-10-06', t: 'ind', lugar: 'Local', hora: '09:30 a 11:00', titulo: 'Identidad visual y señalética de vereda', asistentes: ['mariela', 'agustina'],
      guia: 'Cambiar la percepción externa de que el local está cerrado. Jerarquía visual: mensaje grande primero, marca después.',
      preguntas: ['¿Qué ve alguien que pasa caminando por la vereda?', '¿Dice claramente los horarios y que se puede entrar sin reserva?'],
      objetivos: ['Rediseñar pizarrón de vereda y señalética de puerta.'],
      check: ['Rediseñar tres piezas con el mensaje primero', 'Definir una plantilla reutilizable', 'Revisar qué comunica la vidriera desde la vereda']
    },
    { num: 6, f: '2026-10-12', t: 'ind', lugar: 'Local', hora: '09:30 a 11:00', titulo: 'Sistema de registro diario de ventas y clientes', asistentes: ['mariela', 'agustina'],
      guia: 'Implementar una planilla simple de 4 columnas para reemplazar la memoria por datos reales.',
      preguntas: ['¿Cuántos clientes regulares vinieron esta semana?', '¿Cuál fue el ticket promedio?'],
      objetivos: ['Tener el primer registro diario sistematizado.'],
      check: ['Armar la planilla diaria', 'Definir quién la carga y a qué hora', 'Cargar la primera semana completa']
    },
    { num: 7, f: '2026-10-21', t: 'gru', lugar: 'Célula 4 (Turismo)', hora: '09:30 a 12:00', titulo: 'Cierre de ronda 2: análisis de clientes regulares', asistentes: ['mariela', 'agustina'],
      guia: 'Evaluar los números de las primeras cápsulas con los demás miembros de la célula.',
      preguntas: ['¿Qué cápsula funcionó mejor: el té de amigas o el atardecer?'],
      objetivos: ['Ajustar la propuesta según la respuesta de público.'],
      check: ['Traer los datos de las primeras semanas', 'Comparar recurrencia con el resto de la célula']
    },
    { num: 8, f: '2026-10-29', t: 'ind', lugar: 'Local', hora: '09:30 a 11:00', titulo: 'Lectura de datos y ajuste operativo', asistentes: ['mariela', 'agustina'],
      guia: 'Tomar decisiones basadas en los datos registrados de ventas.',
      preguntas: ['¿Qué productos tienen mayor margen y rotación?'],
      objetivos: ['Consolidar el menú mensual fijo.'],
      check: ['Graficar las tres semanas cargadas', 'Decidir qué cápsula se discontinúa', 'Ajustar precios si hace falta']
    },
    { num: 9, f: '2026-11-02', t: 'ind', lugar: 'Local', hora: '09:30 a 11:00', titulo: 'Cierre de ejes y red con alojamientos', asistentes: ['mariela', 'agustina'],
      guia: 'Conectar Haiku con hoteles boutique y cabañas para derivación de desayunos y meriendas.',
      preguntas: ['¿Qué alojamientos te quedan cerca para coordinar un voucher exclusivo?'],
      objetivos: ['Listar 5 alojamientos clave y armar propuesta.'],
      check: ['Listar alojamientos a contactar', 'Preparar la presentación de 5 minutos']
    },
    { num: 10, f: '2026-11-10', t: 'cie', lugar: 'Acto de cierre', hora: '10:00 a 13:00', titulo: 'Distinción, resultados y rueda de negocios', asistentes: ['leandro', 'adria', 'mariela', 'francisco', 'agustina', 'cesia', 'noelia'],
      guia: 'Acto plenario y cierre del programa con el sector turístico de Esquel.',
      preguntas: ['¿Cuál es la proyección de Haiku para este verano?'],
      objetivos: ['Cerrar acuerdos con prestadores y alojamientos.'],
      check: ['Confirmar asistencia', 'Llevar muestras de pastelería', 'Cerrar al menos una reunión con un alojamiento']
    }
  ],
  tambo: [
    { num: 1, f: '2026-09-22', t: 'ter', lugar: 'Predio (Trevelin)', hora: '09:30 a 12:30', titulo: 'Brainstorming y relevamiento en terreno', asistentes: ['adria', 'noelia'],
      guia: 'Recorrer las 2 hectáreas, evaluar el estado de la construcción colonial histórica y definir la ubicación de la pista de equitación.',
      preguntas: ['¿Qué servicios básicos (agua, luz) están disponibles hoy en la zona de la pista?', '¿Qué inversión mínima se requiere para tener la pista operativa?'],
      objetivos: ['Relevar el predio y fotografiar el edificio histórico.', 'Delimitar el área ecuestre inicial.'],
      check: ['Recorrer las 2 ha completas', 'Fotografiar la construcción histórica y el sótano', 'Marcar la ubicación de la pista', 'Listar los servicios que faltan']
    },
    { num: 2, f: '2026-09-29', t: 'ind', lugar: 'Turismo', hora: '10:00 a 11:30', titulo: 'Priorización de actividades: foco ecuestre', asistentes: ['adria', 'noelia'],
      guia: 'Frenar la dispersión (aromáticas, gallinas, ordeñe) y concentrarse 100% en la equitación como fuente de ingresos inmediata.',
      preguntas: ['¿Qué actividad te genera flujo de fondos más rápido sin gran inversión?'],
      objetivos: ['Ordenar las ideas en una sola lista priorizada.'],
      check: ['Ordenar las ideas en una sola lista priorizada', 'Definir qué arranca ahora y qué queda para la temporada siguiente']
    },
    { num: 3, f: '2026-10-06', t: 'ind', lugar: 'Turismo', hora: '10:00 a 11:30', titulo: 'Esquema económico-financiero para inversor', asistentes: ['adria', 'noelia'],
      guia: 'Armar una planilla simple de ingresos, costos fijos y variables para presentarle a su padre a su regreso de Buenos Aires.',
      preguntas: ['¿Qué monto de inversión inicial se le va a pedir al padre y en cuántos meses se amortiza?'],
      objetivos: ['Tener la matriz económica lista para presentar.'],
      check: ['Completar la matriz simplificada', 'Dejarla en formato presentable para el padre', 'Definir la inversión mínima para arrancar']
    },
    { num: 4, f: '2026-10-13', t: 'ind', lugar: 'Turismo', hora: '10:00 a 11:30', titulo: 'Paquetización de la experiencia ecuestre', asistentes: ['adria', 'noelia'],
      guia: 'Diseñar la experiencia con guion, curva emocional y protocolo de seguridad para turistas.',
      preguntas: ['¿Qué duración debe tener la clase/paseo y qué nivel de destreza requiere?'],
      objetivos: ['Escribir el guion de la actividad ecuestre.'],
      check: ['Escribir el guion de la experiencia', 'Definir duración y cupo', 'Fijar el precio']
    },
    { num: 5, f: '2026-10-20', t: 'ind', lugar: 'Turismo', hora: '10:00 a 11:30', titulo: 'Comunicación y narrativa de macro destino', asistentes: ['adria', 'noelia'],
      guia: 'Aprovechar sus habilidades de marketing digital e integrar la narrativa de Esquel aunque el predio esté en Trevelin.',
      preguntas: ['¿Cómo conectamos la visita al Tambo con el circuito de tulipanes y los atractivos de Esquel?'],
      objetivos: ['Definir el plan de contenido y portfolio.'],
      check: ['Revisar el portfolio de contenidos', 'Escribir el párrafo de presentación con el encuadre de macro destino', 'Definir el plan de contenido']
    },
    { num: 6, f: '2026-10-27', t: 'ind', lugar: 'Turismo', hora: '10:00 a 11:30', titulo: 'Habilitaciones municipales y agrimensura', asistentes: ['adria', 'noelia'],
      guia: 'Seguimiento de trámites municipales, seguros de equitación y deslindes de agrimensura.',
      preguntas: ['¿Qué requisitos exige la municipalidad de Trevelin para actividad ecuestre comercial?'],
      objetivos: ['Iniciar expediente de habilitación.'],
      check: ['Listar los permisos necesarios', 'Iniciar el trámite municipal', 'Pedir presupuesto de agrimensor']
    },
    { num: 7, f: '2026-11-03', t: 'ind', lugar: 'Turismo', hora: '10:00 a 11:30', titulo: 'Ficha comercial para receptivos', asistentes: ['adria', 'noelia'],
      guia: 'Armado de la ficha de comercialización para agencias de Esquel y Trevelin.',
      preguntas: ['¿A qué agencias receptivas les presentaremos el producto en la rueda?'],
      objetivos: ['Cerrar ficha comercial con comisiones.'],
      check: ['Cerrar la ficha comercial', 'Listar receptivos objetivo', 'Preparar la presentación de 5 minutos']
    },
    { num: 8, f: '2026-11-10', t: 'cie', lugar: 'Acto de cierre', hora: '10:00 a 13:00', titulo: 'Distinción, resultados y rueda de negocios', asistentes: ['leandro', 'adria', 'mariela', 'francisco', 'agustina', 'cesia', 'noelia'],
      guia: 'Cierre plenario y rueda comercial.',
      preguntas: ['¿Cuándo inician las primeras clases en la pista?'],
      objetivos: ['Cerrar acuerdos con agencias y hoteles.'],
      check: ['Confirmar asistencia', 'Llevar fotos del predio', 'Cerrar al menos una reunión con un receptivo']
    }
  ],
  nire: [
    { num: 1, f: '2026-09-15', t: 'ter', lugar: 'Predio Altos de Nant y Fall', hora: '09:30 a 12:30', titulo: 'Relevamiento en terreno y diagnóstico de predio', asistentes: ['adria', 'noelia'],
      guia: 'Relevar in situ las 2 hectáreas, el sendero de circunvalación, la casita de descanso y el estado del camino de ripio de 1.500 m. Evaluar áreas de microflora y puntos de mayor silencio.',
      preguntas: ['¿Dónde se siente el mayor silencio dentro del bosque?', '¿Cómo reacciona el suelo del callejón con lluvia?', '¿Qué microflora observamos en el sendero corto?'],
      objetivos: ['Relevar medidas y recorrido del sendero.', 'Evaluar puntos críticos del callejón de acceso.', 'Validar disponibilidad de la casita y servicios.'],
      check: ['Fotografiar puntos críticos del acceso', 'Medir los tramos del sendero interno', 'Probar conectividad y equipamiento de la casita', 'Tomar muestras de líquenes y hojas de ñire']
    },
    { num: 2, f: '2026-09-18', t: 'ind', lugar: 'Turismo', hora: '11:00 a 12:30', titulo: 'Estrategia de macrodestino y nodo Laguna La Zeta', asistentes: ['leandro', 'adria', 'noelia'],
      guia: 'Definir el circuito periurbano en Laguna La Zeta para tener presencia activa y legitimada dentro del ejido municipal de Esquel, capturando turistas de tarde.',
      preguntas: ['¿Qué rincón de La Zeta ofrece la menor contaminación sonora y mejor resguardo de viento?', '¿Cómo adaptamos los tiempos para turistas en tránsito en Esquel?'],
      objetivos: ['Trazar el mapa del circuito La Zeta (máximo 1,5 km).', 'Redactar la fundamentación de macrodestino para autoridades.', 'Definir frecuencia de salidas semanales.'],
      check: ['Trazar el recorrido de La Zeta', 'Redactar la fundamentación institucional del doble nodo', 'Definir días y horarios de salidas en Esquel']
    },
    { num: 3, f: '2026-09-23', t: 'ind', lugar: 'Turismo', hora: '10:00 a 12:00', titulo: 'Estructuración del guion sensorial e inmersión', asistentes: ['adria', 'noelia'],
      guia: 'Redactar el guion de la experiencia con las consignas sensoriales, ejercicios de respiración, micro-observación y tiempos de pausa.',
      preguntas: ['¿Cómo rompemos el ritmo acelerado del turista en los primeros 10 minutos?', '¿Qué metáforas usamos para explicar los líquenes y el ñirantal?'],
      objetivos: ['Escribir el guion de 5 estaciones.', 'Estandarizar la bienvenida y cierre ceremonial.', 'Incorporar el protocolo de recolección de infusión.'],
      check: ['Escribir las 5 estaciones del recorrido', 'Pautar los momentos de silencio absoluto', 'Diseñar la ceremonia de cierre con infusión caliente']
    },
    { num: 4, f: '2026-09-30', t: 'gru', lugar: 'Célula 2 (Turismo)', hora: '09:30 a 12:30', titulo: 'Gestión de predios rurales y costeo (Célula 2)', asistentes: ['adria', 'noelia'],
      guia: 'Encuentro grupal de la Célula 2 (Campo y gran superficie). Poner en común costos de mantenimiento rural, traslados, seguros de excursionistas y alianzas cruzadas en el corredor.',
      preguntas: ['¿Cómo nos complementamos en el circuito de Nant y Fall y la Ruta 259?', '¿Podemos compartir transportes o derivar visitantes entre chacras?'],
      objetivos: ['Completar matriz de costos fijos y variables.', 'Identificar cruces con Viñas del Nant y Fall y Cascada.', 'Validar seguro colectivo de turismo activo.'],
      check: ['Llevar la matriz de costos completa', 'Identificar al menos 2 cruces de derivación con la célula', 'Revisar pólizas de seguro de turismo activo']
    },
    { num: 5, f: '2026-10-06', t: 'ter', lugar: 'Laguna La Zeta', hora: '10:00 a 12:30', titulo: 'Salida piloto y prueba de campo en La Zeta', asistentes: ['adria', 'noelia'],
      guia: 'Ejecutar el guion sensorial completo con público testigo para medir tiempos reales, silencios y recepción emocional.',
      preguntas: ['¿Los participantes lograron desconectarse del celular?', '¿Qué estación resultó más emotiva?', '¿Cómo funcionó la infusión servida al final?'],
      objetivos: ['Validar tiempos reales del guion.', 'Recoger devoluciones sinceras de los participantes.', 'Tomar fotografías de calidad para el banco de imágenes.'],
      check: ['Cronometrar cada parada del sendero', 'Registrar planilla de feedback de los asistentes', 'Probar logística de calentamiento de agua para infusión al aire libre']
    },
    { num: 6, f: '2026-10-14', t: 'ind', lugar: 'Turismo', hora: '10:00 a 12:00', titulo: 'Turismo accesible familiar y souvenir botánico', asistentes: ['adria', 'noelia'],
      guia: 'Formalizar la cápsula para familias con personas con discapacidad y terminar el prototipo del packaging de la infusión de hojas de ñire.',
      preguntas: ['¿Qué adaptaciones requiere el sendero para paso asistido o personas con movilidad reducida?', '¿Cómo comunicamos la propuesta accesible con calidez y sin estigmatizar?'],
      objetivos: ['Redactar la ficha de Turismo Accesible Familiar.', 'Diseñar la etiqueta de la bolsita de té de ñire.', 'Articular con la Dirección de Discapacidad / Inclusión.'],
      check: ['Redactar el protocolo para familias con miembros con discapacidad', 'Diseñar la etiqueta del souvenir botánico', 'Definir el método de secado y conservación de las hojas']
    },
    { num: 7, f: '2026-10-21', t: 'ind', lugar: 'Turismo', hora: '10:00 a 12:00', titulo: 'Ficha comercial y venta a receptivos y hoteles', asistentes: ['adria', 'noelia'],
      guia: 'Cerrar tarifas, comisiones para agencias (15-20%) y armar el material gráfico/digital para recepciones de hoteles boutique de Esquel.',
      preguntas: ['¿Cuánto le dejamos al recepcionista o agencia por derivación confirmada?', '¿Cuál es el canal y mensaje de reserva inmediata?'],
      objetivos: ['Cerrar tarifario definitivo y comisiones.', 'Imprimir fichas comerciales en alta calidad.', 'Configurar WhatsApp Business con catálogo.'],
      check: ['Cerrar el tarifario final con comisiones', 'Redactar la ficha de una carilla para agencias', 'Listar los 5 hoteles boutique donde presentar la propuesta']
    },
    { num: 8, f: '2026-11-10', t: 'cie', lugar: 'Acto de cierre', hora: '10:00 a 13:00', titulo: 'Distinción, resultados y rueda de negocios', asistentes: ['leandro', 'adria', 'mariela', 'francisco', 'agustina', 'cesia', 'noelia'],
      guia: 'Acto plenario y cierre del programa con el sector turístico comarcal. Presentación del producto ante agencias, prestadores y prensa.',
      preguntas: ['¿Qué convenios comerciales quedan cerrados para el verano 2027?'],
      objetivos: ['Cerrar al menos dos convenios de derivación con agencias u hoteles.', 'Presentar el souvenir botánico en el stand.'],
      check: ['Confirmar asistencia al plenario', 'Llevar muestras de la infusión de ñire para degustación', 'Cerrar al menos una alianza comercial en la rueda']
    }
  ],
  corcho: [
    { num: 1, f: '2026-09-17', t: 'ter', lugar: 'Taller Los Sauces (Calle 44 Casa 28)', hora: '15:00 a 17:00', titulo: 'Relevamiento en terreno: taller barrial y entorno La Trochita', asistentes: ['leandro', 'agustina'],
      guia: 'Relevar el espacio físico del taller, layout de herramientas, rincón de recepción y mate, acceso desde la estación La Trochita y callejón hacia el cementerio. Probar el dulce casero y evaluar opciones de packaging.',
      preguntas: ['¿Dónde ubicamos a los turistas y a los niños para que estén seguros sin riesgo en el taller?', '¿Cómo integramos la degustación del dulce casero en el espacio de bienvenida?', '¿Qué estado mecánico tiene la bicicleta de competición y qué herramientas tenemos para mostrar?'],
      objetivos: ['Medir y delimitar el sector de visita en el taller.', 'Fotografiar el taller y el acceso barrial.', 'Relevar estado de la bicicleta y herramientas.', 'Degustar el dulce casero y evaluar opciones de packaging.'],
      check: ['Medir el espacio disponible para recepción', 'Fotografiar puntos críticos de acceso y vías del tren', 'Relevar herramientas y elementos de seguridad en taller', 'Definir el rincón de mateada y degustación de dulces']
    },
    { num: 2, f: '2026-09-22', t: 'ind', lugar: 'Turismo', hora: '15:00 a 16:30', titulo: 'Arquitectura web y benchmarking internacional', asistentes: ['leandro', 'agustina'],
      guia: 'Revisar el material de referencias internacionales recopilado por la familia (webs de EE.UU., Europa, etc.), estructurar el árbol de contenidos de la página web y redactar el borrador del relato de vida de Corcho.',
      preguntas: ['¿Qué secciones tendrá la web (Inicio, Historia de Corcho, Circuitos, Taller, Reservas)?', '¿Qué llamados a la acción priorizamos para que el turista reserve por WhatsApp?'],
      objetivos: ['Recibir el documento borrador con la historia deportiva de Corcho.', 'Seleccionar referencias de diseño y animaciones.', 'Definir estructura de hosting, dominio y layout que programará Leandro.'],
      check: ['Revisar referencias web internacionales enviadas', 'Estructurar el árbol de navegación del sitio web', 'Redactar la biografía deportiva desde los 11 años', 'Configurar llamados a la acción para reservas']
    },
    { num: 3, f: '2026-09-24', t: 'ind', lugar: 'Turismo', hora: '15:00 a 16:30', titulo: 'Estrategia de flota puente, seguridad y normativa', asistentes: ['leandro', 'agustina'],
      guia: 'Definir la estrategia de provisión de bicicletas para la temporada de prueba, relevar costos de cascos homologados y encuadrar el seguro de turismo activo.',
      preguntas: ['¿A qué bicicleterías o prestadores podemos acudir para un acuerdo puente de alquiler?', '¿Cuánto cuesta el seguro de accidentes personales por excursionista por día?'],
      objetivos: ['Mapear 3 alternativas de provisión de 6 bicicletas.', 'Cotizar póliza de seguro de turismo alternativo.', 'Iniciar expediente de registro de prestador en la Subsecretaría de Turismo.'],
      check: ['Contactar a 2 bicicleterías locales para convenio puente', 'Cotizar kit de 8 cascos homologados de diversos talles', 'Pedir presupuesto a aseguradora para turismo activo', 'Armar borrador de ficha médica y deslinde de responsabilidad']
    },
    { num: 4, f: '2026-09-30', t: 'gru', lugar: 'Célula 4 (Melipal)', hora: '09:30 a 12:30', titulo: 'Canal, volumen y comercialización cruzada (Célula 4)', asistentes: ['leandro', 'mariela', 'agustina'],
      guia: 'Encuentro grupal de la Célula 4 (Canal y volumen). Analizar canales de venta, alianzas cruzadas entre prestadores de excursiones y agencias receptivas, y fijación de comisiones de intermediación.',
      preguntas: ['¿Cómo puede Yamamori Travel o True Patagonia incluir las guiadas de Corcho en sus paquetes de destino?', '¿Qué sinergias hay entre la merienda de Haiku y el cicloturismo?'],
      objetivos: ['Presentar la propuesta de Corcho Bikes a los colegas de célula.', 'Analizar esquema de comisiones del 15% al 20%.', 'Identificar derivaciones cruzadas de turistas.'],
      check: ['Llevar la propuesta resumida de los 2 circuitos', 'Acordar con al menos 1 agencia receptiva de la célula una salida de prueba', 'Validar el esquema de comisiones de intermediación']
    },
    { num: 5, f: '2026-10-10', t: 'ter', lugar: 'La Trochita y Taller', hora: '14:30 a 17:30', titulo: 'Rodaje audiovisual profesional con dron y 4K', asistentes: ['leandro', 'agustina'],
      guia: 'Filmar el material publicitario oficial con cámaras 4K y dron en vuelo rasante sobre las vías del tren La Trochita y tomas de oficio y detalle en el taller.',
      preguntas: ['¿Cuáles son los ángulos más impactantes del pedaleo junto a los rieles históricos?', '¿Cómo capturamos la emoción y la maestría mecánica de Corcho en video?'],
      objetivos: ['Filmar secuencia de Corcho pedaleando junto a las vías con seguimiento de dron.', 'Realizar tomas macro de ajuste de rayos, piñones y grasa en el taller.', 'Grabar mensaje de invitación a cámara.'],
      check: ['Coordinar con el camarógrafo y piloto de dron de Turismo', 'Preparar la bicicleta de competición impecable y ropa deportiva', 'Grabar tomas de taller, mates y herramientas', 'Hacer backup del material grabado en crudo']
    },
    { num: 6, f: '2026-10-15', t: 'ter', lugar: 'Taller y Mirador de las Vías', hora: '15:00 a 17:30', titulo: 'Salida piloto y ensayo general de circuito familiar', asistentes: ['leandro', 'agustina'],
      guia: 'Ejecutar la experiencia familiar completa en condiciones reales para cronometrar la clínica para niños, la merienda con mate y dulce para los adultos, y validar protocolos de seguridad.',
      preguntas: ['¿Los niños comprendieron y disfrutaron la mecánica básica?', '¿Los padres sintieron que tuvieron un verdadero momento de descanso y relax?', '¿Cómo resultó el tiempo total?'],
      objetivos: ['Cronometrar cada bloque de la experiencia.', 'Evaluar el funcionamiento de las bicis y cascos.', 'Recabar feedback honesto de la familia invitada.', 'Ajustar detalles del guion.'],
      check: ['Convocar a una familia con 2 niños para la prueba', 'Probar el kit de cascos y señalización', 'Servir la merienda con dulce casero y mate cocido', 'Completar encuesta de satisfacción con los invitados']
    },
    { num: 7, f: '2026-10-27', t: 'ind', lugar: 'Turismo', hora: '15:00 a 16:30', titulo: 'Presentación web oficial, tarifario y ficha comercial', asistentes: ['leandro', 'agustina'],
      guia: 'Presentar el sitio web terminado y publicado online, validar el prototipo del souvenir (lapicero reciclado + dulce) y cerrar el tarifario final para el sector comercial.',
      preguntas: ['¿Está operativo el botón de reserva directa por WhatsApp?', '¿El precio cubre todos los costos fijos y variables dejando margen de ganancia neto?'],
      objetivos: ['Revisar la web en vivo en computadoras y celulares.', 'Validar el prototipo del souvenir reciclado.', 'Imprimir 10 fichas comerciales para distribuir en hoteles.', 'Configurar catálogo de WhatsApp Business.'],
      check: ['Testear funcionamiento del sitio web y formularios', 'Aprobar el packaging del souvenir reciclado con dulce', 'Fijar precio por persona y paquete familiar', 'Imprimir 10 fichas comerciales para el sector receptivo']
    },
    { num: 8, f: '2026-11-10', t: 'cie', lugar: 'Acto de cierre', hora: '10:00 a 13:00', titulo: 'Distinción, resultados y rueda de negocios', asistentes: ['leandro', 'adria', 'mariela', 'francisco', 'agustina', 'cesia', 'noelia'],
      guia: 'Cierre plenario y rueda comercial con el sector turístico comarcal. Presentación del producto ante agencias, prestadores y prensa.',
      preguntas: ['¿Qué convenios comerciales quedan cerrados para el verano 2027?'],
      objetivos: ['Cerrar al menos dos convenios de derivación con agencias u hoteles.', 'Presentar el sitio web y el video de dron en pantalla gigante.'],
      check: ['Confirmar asistencia al plenario con la familia', 'Montar stand con bici histórica, piezas recicladas y dulces', 'Cerrar al menos un convenio formal en la rueda de negocios']
    }
  ],
  vicotita: [
    { num: 1, f: '2026-09-16', t: 'ter', lugar: 'Taller Chanico Navarro (Pintor Antúnez 968)', hora: '10:00 a 12:00', titulo: 'Relevamiento en terreno: taller, procesos y muestras históricas', asistentes: ['mariela', 'cesia'],
      guia: 'Relevar las maquinarias de serigrafía, shablonadora, stock de telas impermeables, herramientas de encuadernación y muestras de productos históricos.',
      preguntas: ['¿Cuáles son los productos con menor tiempo de confección y mejor margen?', '¿Qué capacidad de almacenamiento y aprovisionamiento de telas tienen hoy?'],
      objetivos: ['Inventariar maquinaria y herramientas de taller.', 'Fotografiar muestras de encuadernación y marroquinería.', 'Analizar compras de insumos por volumen.'],
      check: ['Relevar máquinas de coser, shablones y mesas de corte', 'Inventariar telas impermeables y tipos de papel', 'Fotografiar muestras históricas y piezas de Puro Diseño', 'Definir el flujo de trabajo entre ambos domicilios']
    },
    { num: 2, f: '2026-09-23', t: 'ind', lugar: 'Turismo', hora: '10:00 a 11:30', titulo: 'Storytelling de marca, marbete narrativo y código QR', asistentes: ['leandro', 'mariela', 'cesia'],
      guia: 'Traducir la profundidad del relato mapuche contemporáneo en un texto breve para marbetes y tarjetas de autenticidad que acompañen cada pieza.',
      preguntas: ['¿Cómo explicamos el símbolo del Quñegüe o el punto nimín en 3 oraciones sencillas?', '¿Qué llamado a la acción incluimos en el código QR?'],
      objetivos: ['Redactar el texto del marbete de autor.', 'Diseñar el boceto de la tarjeta con código QR.', 'Cotizar impresión con imprentas locales.'],
      check: ['Redactar el texto del marbete en español y mapuzugun', 'Diseñar el mockup de tarjeta de autenticidad con QR', 'Configurar landing/enlace con galería del proceso artesanal', 'Seleccionar imprenta local para tirada de prueba']
    },
    { num: 3, f: '2026-09-25', t: 'ind', lugar: 'Turismo', hora: '10:00 a 11:30', titulo: 'Prototipado de la Bitácora de Campo y Registro de Montaña', asistentes: ['mariela', 'cesia'],
      guia: 'Definir el formato físico, encuadernación y contenido interior de la libreta de registro de senderos y montaña para prestadores.',
      preguntas: ['¿Qué tamaño es el más práctico para llevar en la mochila de trekking (A5 o A6)?', '¿Qué secciones de coordenadas y apuntes incluimos en las hojas?'],
      objetivos: ['Definir gramaje de hojas y tipo de encuadernación (cosida o brocheada).', 'Redactar el contenido de las páginas interiores.', 'Armar el primer boceto físico.'],
      check: ['Definir medidas estándar de corte A5/A6', 'Diagramar la grilla de coordenadas y mapa de senderos', 'Seleccionar micro-poesías de autores patagónicos', 'Confeccionar el primer ejemplar de prueba']
    },
    { num: 4, f: '2026-09-30', t: 'gru', lugar: 'Célula 1 (Melipal)', hora: '09:30 a 12:00', titulo: 'Costeo, volumen y consignación (Célula 1)', asistentes: ['mariela', 'cesia'],
      guia: 'Encuentro grupal de la Célula 1 (Oficio y pieza). Poner en común matrices de costeo, valor de hora de taller, esquemas de comisiones para revendedores y acuerdos de no canibalización.',
      preguntas: ['¿Qué porcentaje es justo dejarle a una recepción de hotel o tienda regional?', '¿Conviene vender en firme con descuento o en consignación?'],
      objetivos: ['Comparar costos fijos y variables con otros artesanos.', 'Validar escala de descuentos mayoristas.', 'Evaluar alianzas de empaque conjunto.'],
      check: ['Llevar la planilla de costeo por producto terminada', 'Comparar márgenes con Los Crovas, Carpintero y Patagonia Retro', 'Fijar tope de comisión para terceros (20% a 25%)']
    },
    { num: 5, f: '2026-10-07', t: 'ind', lugar: 'Turismo', hora: '10:00 a 11:30', titulo: 'Diseño y estructura del catálogo B2B para prestadores', asistentes: ['mariela', 'cesia'],
      guia: 'Estructurar el catálogo comercial digital para presentar a hoteles, agencias y corporativos con mockups de personalización y co-branding.',
      preguntas: ['¿Qué 6 productos insignia colocamos en el catálogo?', '¿Cómo explicamos los tiempos de producción (7 a 15 días) para evitar reclamos?'],
      objetivos: ['Seleccionar fotos de alta calidad de los productos.', 'Redactar las fichas técnicas y condiciones comerciales.', 'Montar el PDF interactivo.'],
      check: ['Seleccionar las fotos definitivas de la línea de temporada', 'Redactar escalas de precios para 20, 50 y 100 unidades', 'Armar el PDF interactivo listo para enviar por WhatsApp y mail', 'Definir cláusula de seña del 50% y plazos de entrega']
    },
    { num: 6, f: '2026-10-15', t: 'ter', lugar: 'Taller de Encuadernación (Majo)', hora: '15:00 a 17:00', titulo: 'Validación de prototipos y proto-armado de taller abierto', asistentes: ['mariela', 'cesia'],
      guia: 'Reunión conjunta con ambas hermanas para revisar los prototipos de la Bitácora de Campo terminados y evaluar la viabilidad de la experiencia inmersiva de taller.',
      preguntas: ['¿Cómo responde el diseño gráfico interior de la libreta?', '¿Es viable abrir el taller para 4 o 6 personas en temporadas especiales?'],
      objetivos: ['Evaluar físicamente la bitácora terminada.', 'Validar el circuito de serigrafía y estampado en vivo.', 'Definir pautas del taller abierto de temporada.'],
      check: ['Revisar 3 bitácoras terminadas con encuadernación artesanal', 'Probar la experiencia de estampado en vivo con matriz doble', 'Redactar el guion de 1h30m para grupos reducidos', 'Establecer cupos máximos de 6 personas para octubre']
    },
    { num: 7, f: '2026-10-28', t: 'ind', lugar: 'Turismo', hora: '10:00 a 11:30', titulo: 'Rueda de contactos comerciales y colocación en hoteles', asistentes: ['mariela', 'cesia'],
      guia: 'Presentar el catálogo B2B a 5 establecimientos turísticos seleccionados de Esquel para concertar pedidos mayoristas de merchandising de autor.',
      preguntas: ['¿Qué hoteles boutique y lodges de pesca muestran interés en co-brandear bitácoras o estuches?', '¿Qué pedidos de muestra se entregan?'],
      objetivos: ['Enviar catálogo personalizado a los 5 establecimientos objetivo.', 'Entregar muestras físicas con marbete narrativo.', 'Realizar seguimiento de pedidos.'],
      check: ['Mapear 5 establecimientos objetivo de Esquel', 'Enviar el catálogo comercial con nota formal de presentación', 'Dejar muestras físicas con marbete en 2 hoteles boutique', 'Registrar devoluciones y solicitudes de cotización']
    },
    { num: 8, f: '2026-11-10', t: 'cie', lugar: 'Acto de cierre', hora: '10:00 a 13:00', titulo: 'Distinción, resultados y rueda de negocios', asistentes: ['leandro', 'adria', 'mariela', 'francisco', 'agustina', 'cesia', 'noelia'],
      guia: 'Cierre plenario y rueda comercial con el sector turístico comarcal. Presentación del producto ante agencias, prestadores y prensa.',
      preguntas: ['¿Cuántos convenios comerciales de provisión formal se firman en la rueda de negocios?'],
      objetivos: ['Cerrar al menos dos convenios de provisión con alojamientos o agencias.', 'Exhibir la colección completa en el stand oficial.'],
      check: ['Confirmar asistencia de ambas socias al plenario', 'Montar stand de exhibición con marbetes y packaging terminado', 'Cerrar al menos un acuerdo comercial formal en la rueda']
    }
  ],
  laberin: [
    { num: 1, f: '2026-09-15', t: 'ter', lugar: 'Chacra Austin (Ruta 259 km 4)', hora: '15:00 a 17:30', titulo: 'Relevamiento en terreno: replanteo, suelo y punto cero del laberinto', asistentes: ['adria', 'noelia', 'leandro'],
      guia: 'Relevar el terreno destinado al laberinto, verificar la perforación de agua, estado del camino de servidumbre y platea del salón de eventos. Fijar el punto cero de replanteo.',
      preguntas: ['¿Dónde fijamos el punto central de replanteo del laberinto?', '¿Cómo resolvemos la curva de la máquina vial que invadió la parcela?', '¿Qué presión y caudal entrega la bomba de agua de la perforación?'],
      objetivos: ['Medir con cinta y GPS el perímetro de las 2 hectáreas.', 'Fotografiar el suelo, platea y puntos de acceso.', 'Probar caudal de la perforación.', 'Tomar 4 muestras de suelo para envío a análisis.'],
      check: ['Medir perímetro del predio asignado', 'Verificar caudal de la perforación de agua', 'Fotografiar la platea del salón de eventos', 'Extraer 4 muestras de suelo para análisis agronómico']
    },
    { num: 2, f: '2026-09-22', t: 'ind', lugar: 'Turismo', hora: '10:00 a 11:30', titulo: 'Modelo agronómico: suelo, enmiendas y vivero de esquejes', asistentes: ['adria', 'noelia'],
      guia: 'Analizar los resultados del suelo, estructurar el plan de enmienda con abono de oveja y diseñar el módulo de multiplicación por esquejes de ligustro.',
      preguntas: ['¿Cuántas camionadas de tierra negra y abono se necesitan para la franja de plantación?', '¿Cómo organizamos la recolección y estacado de 4.000 esquejes de ligustro?'],
      objetivos: ['Revisar informe preliminar de suelo.', 'Diseñar el bancal del vivero de esquejes.', 'Cotizar mangueras de goteo y goteros autocompensantes.', 'Planificar la parcela testigo.'],
      check: ['Calcular volumen de abono de oveja necesario', 'Diseñar módulo de vivero para 4.000 esquejes', 'Cotizar sistema de riego por goteo', 'Definir protocolo de enraizante natural de sauce']
    },
    { num: 3, f: '2026-09-29', t: 'ind', lugar: 'Turismo', hora: '10:00 a 12:00', titulo: 'Geometría del laberinto y consulta técnica paisajística', asistentes: ['adria', 'noelia'],
      guia: 'Definir la traza geométrica final del laberinto (ancho de senderos, radio exterior, plazoleta central) y calcular metros lineales de cerco vivo.',
      preguntas: ['¿Qué distancia exacta entre plantas garantiza cierre tupido sin asfixia de raíces?', '¿Qué elementos lúdicos o miradores incluimos en el centro?'],
      objetivos: ['Dibujar el plano del laberinto a escala.', 'Calcular cantidad exacta de plantas necesarias.', 'Dimensionar el circuito hidráulico de riego.', 'Fijar puntos de mojones en plano.'],
      check: ['Definir ancho de senderos (1,80 a 2 m)', 'Calcular metros lineales totales de cerco vivo', 'Fijar plazoleta central con mirador panorámico', 'Establecer puntos de bifurcación y caminos ciegos']
    },
    { num: 4, f: '2026-09-30', t: 'gru', lugar: 'Célula 2 (Melipal)', hora: '09:30 a 12:30', titulo: 'Predios rurales y alianzas de corredor (Célula 2)', asistentes: ['adria', 'noelia'],
      guia: 'Encuentro grupal de la Célula 2 (Campo y gran superficie). Poner en común costos de infraestructura rural, manejo de visitantes en chacras, alianzas de derivación mutua en la Ruta 259 y seguros colectivos.',
      preguntas: ['¿Cómo conectamos el laberinto con las cabalgatas de Lucero o el tambo de Sofía?', '¿Qué paquetes combinados de día de campo podemos prefigurar en la Ruta 259?'],
      objetivos: ['Presentar el proyecto a los colegas de célula.', 'Mapear circuito de la Ruta 259.', 'Analizar normativas de turismo rural y de recreación al aire libre.'],
      check: ['Presentar el Master Plan del laberinto ante la célula', 'Mapear sinergias con Tambo, Margherita y Lucero', 'Analizar requisitos de habilitación de turismo rural']
    },
    { num: 5, f: '2026-10-08', t: 'ter', lugar: 'Chacra Austin (Ruta 259 km 4)', hora: '15:00 a 17:30', titulo: 'Supervisión de vivero de esquejes y parcela testigo', asistentes: ['adria', 'noelia'],
      guia: 'Verificar la implantación de la parcela testigo con enmienda orgánica y supervisar el enraizamiento de los primeros lotes de esquejes bajo riego por goteo.',
      preguntas: ['¿Qué brotación muestran los esquejes con enraizante de sauce?', '¿Cómo responde la humedad del suelo tras la aplicación de abono de oveja?'],
      objetivos: ['Medir desarrollo de brotes en la parcela testigo.', 'Inspeccionar sistema de riego y presión de goteros.', 'Controlar estado del cerco anti-liebres.', 'Tomar registros fotográficos.'],
      check: ['Medir longitud de brotes en parcela testigo', 'Controlar funcionamiento de goteros y presión de agua', 'Inspeccionar cerco con malla anti-liebres', 'Verificar avance del salón de té/eventos']
    },
    { num: 6, f: '2026-10-20', t: 'ind', lugar: 'Turismo', hora: '10:00 a 11:30', titulo: 'Estructuración de servicios puente: casa de té y cabalgatas', asistentes: ['adria', 'noelia'],
      guia: 'Estructurar la propuesta comercial de los servicios puente: apertura de la casa de té de campo, visitas al predio y paseos a caballo para la temporada 2027.',
      preguntas: ['¿Qué menú de repostería y té artesanal ofrecerá Marta?', '¿Qué tarifa y protocolo tendrán las cabalgatas de faldeo guiadas por Joaquín?'],
      objetivos: ['Armar la carta gastronómica de la casa de té.', 'Definir circuito y seguridad de cabalgatas.', 'Redactar ficha comercial de actividades de campo.'],
      check: ['Definir propuesta de repostería casera y té de campo', 'Trazar el circuito de cabalgatas de faldeo con los 6 caballos', 'Fijar tarifas de la casa de té y paseos ecuestres', 'Redactar deslinde de responsabilidad para cabalgatas']
    },
    { num: 7, f: '2026-11-03', t: 'ind', lugar: 'Turismo', hora: '10:00 a 11:30', titulo: 'Prefactibilidad económica y presupuesto por etapas', asistentes: ['adria', 'noelia'],
      guia: 'Consolidar la matriz económica del proyecto a 3 años, cerrar el presupuesto por etapas y preparar el dossier para inversores o financiamiento productivo.',
      preguntas: ['¿Cuál es la inversión neta de la Fase 1 (vivero y salón de té)?', '¿En qué mes proyectamos el punto de equilibrio operativo?'],
      objetivos: ['Cerrar la planilla de costos y flujo de fondos.', 'Redactar el resumen ejecutivo de prefactibilidad.', 'Preparar presentación visual para la ronda de negocios.'],
      check: ['Consolidar la planilla de costos a 3 años', 'Redactar el resumen ejecutivo de prefactibilidad', 'Armar lámina visual del Master Plan para inversores', 'Preparar discurso de presentación de 3 minutos']
    },
    { num: 8, f: '2026-11-10', t: 'cie', lugar: 'Acto de cierre', hora: '10:00 a 14:00', titulo: 'Distinción, presentación del Master Plan y rueda de negocios', asistentes: ['leandro', 'adria', 'mariela', 'francisco', 'agustina', 'cesia', 'noelia'],
      guia: 'Cierre plenario y rueda comercial con el sector turístico comarcal. Presentación del Master Plan ante agencias, prestadores, autoridades y prensa.',
      preguntas: ['¿Qué alianzas o apoyos institucionales se concretan para la ejecución de la Fase 1?'],
      objetivos: ['Presentar el Master Plan del laberinto en el stand.', 'Ofrecer degustación de repostería de la futura casa de té.', 'Cerrar al menos una carta de intención con el sector receptivo.'],
      check: ['Montar maqueta/lámina del Master Plan en el stand', 'Llevar plantines de ligustro y muestras de repostería casera', 'Cerrar al menos una alianza comercial en la rueda']
    }
  ],
  truepat: [
    { num: 1, f: '2026-09-18', t: 'ind', lugar: 'Oficina Turismo', hora: '10:00 a 12:30', titulo: 'Diagnóstico en terreno, deconstrucción de marca y reposicionamiento de autor', asistentes: ['mariela', 'noelia', 'leandro'],
      guia: 'Deconstruir el nombre provisorio "True Patagonia" y acordar la nueva denominación de autor. Inventariar la red de contactos existente (maestras de telar en Melipal, hilanderas, chacras conocidas en Percy y Nahuelpan). Fijar el perfil de la persona pública de Cecilia.',
      preguntas: ['¿Por qué el nombre en inglés aleja al público que busca identidad ancestral?', '¿Cómo resumimos la historia de Cecilia en 30 segundos de manera magnética?', '¿Cuáles son los 3 saberes que sí o sí deben estar en el retiro?'],
      objetivos: ['Completar ficha de relevamiento de contactos de campo.', 'Proponer 3 opciones de denominación de marca con anclaje patagónico.', 'Definir público objetivo (mujeres 35-65 años, apasionadas del quehacer textil, turismo regenerativo y cultural).'],
      check: ['Mapear artesanas, hilanderas y predios rurales aliados', 'Definir nueva denominación de marca de autor', 'Diseñar perfil de la persona pública de Cecilia']
    },
    { num: 2, f: '2026-09-22', t: 'ind', lugar: 'Taller Cecilia (Los Álamos)', hora: '14:00 a 16:30', titulo: 'Curaduría del itinerario vivencial y diseño del guion emocional', asistentes: ['noelia'],
      guia: 'Diagramar el cronograma paso a paso del retiro de 4 días / 3 noches. Diseñar la curva dramática de la experiencia: alternancia de actividades manuales, tiempos de descanso, comidas tradicionales y momentos de asombro.',
      preguntas: ['¿Cómo evitamos que la jornada de hilado resulte agotadora para quien nunca agarró un huso?', '¿En qué momento del día se produce la revelación del color en la olla de tintes?', '¿Qué historia de fogón compartimos a la noche?'],
      objetivos: ['Redactar el guion cronológico del Día 1 al Día 4.', 'Cronometrar tiempos de taller (máximo 3 horas de trabajo manual por jornada).', 'Programar la Cápsula Full Day de Crochet y Naturaleza como producto complementario.'],
      check: ['Redactar guion paso a paso de las 4 jornadas', 'Establecer descansos y pausas sensoriales', 'Definir la cápsula de campo Patrones de Montaña y Crochet']
    },
    { num: 3, f: '2026-09-28', t: 'ter', lugar: 'Las Margaritas (Nahuelpan) y Percy', hora: '09:30 a 13:00', titulo: 'Prospección en territorio y acuerdos con anfitriones rurales', asistentes: ['mariela', 'noelia'],
      guia: 'Relevar in situ las instalaciones de Establecimiento Las Margaritas y evaluar la articulación entre emprendimientos de la misma cohorte. Visitar chacras ovinas aliadas para definir el espacio de esquila, demostración y almuerzo criollo.',
      preguntas: ['¿Las Margaritas cuenta con capacidad para alojar o funciona mejor como locación de talleres y contacto con animales?', '¿Qué infraestructura básica de baños y resguardo de viento se requiere en Percy?'],
      objetivos: ['Completar acta de acuerdo preliminar con Las Margaritas.', 'Evaluar estado de accesos vehiculares para transfers de turistas.', 'Determinar el canon diario por uso de instalaciones y atención campesina.'],
      check: ['Relevar predio Las Margaritas con Lorena Cogos', 'Inspeccionar chacras ovinas en Alto Río Percy', 'Fijar canon por uso de predio y atención campesina']
    },
    { num: 4, f: '2026-09-30', t: 'gru', lugar: 'Célula 4 (Melipal)', hora: '09:30 a 12:30', titulo: 'Costeo, punto de equilibrio y tarifario (Célula 4)', asistentes: ['mariela', 'noelia', 'agustina'],
      guia: 'Poner en común la estructura de costos de los paquetes receptivos y retiros. Calcular costos fijos y variables (alojamiento, transfers, insumos de telar/tintes, honorarios de artesanas campesinas, coordinación de Cecilia y comisión de agencia).',
      preguntas: ['¿Cuál es el costo directo por persona y cuánto representa el honorario de facilitación de Cecilia?', '¿Qué precio final en dólares y pesos resiste el segmento ABC1 nacional e internacional?', '¿A partir de cuántos pasajeros se cubre el punto de equilibrio?'],
      objetivos: ['Planilla de costeo integral terminada.', 'Fijar punto de equilibrio en 4 pasajeros.', 'Definir esquema de comisión para agencias de viajes (20% margen receptivo sobre tarifa neta).'],
      check: ['Completar matriz de costeo integral', 'Determinar punto de equilibrio (4 pasajeros)', 'Fijar tarifario B2C y tarifa neta B2B con 20% margen']
    },
    { num: 5, f: '2026-10-08', t: 'ind', lugar: 'Oficina Turismo', hora: '10:00 a 12:30', titulo: 'Diseño editorial del souvenir físico y packaging de bienvenida', asistentes: ['noelia'],
      guia: 'Revisar la maqueta editorial de la "Bitácora Textil y Carpeta de Saberes" diseñada por Cecilia. Definir proveedores de impresión local en papel reciclado/kraft y armar el packaging de bienvenida (bolsa de lienzo con huso rústico y vellón crudo).',
      preguntas: ['¿Cómo estructuramos las fichas botánicas para que el visitante pueda anotar sus propias recetas de tinte?', '¿Qué piezas componen el kit inicial entregado al llegar a Esquel?'],
      objetivos: ['Prototipo impreso de la Bitácora de Saberes.', 'Presupuestar impresión de 50 ejemplares con imprentas de Esquel.', 'Diseñar etiquetas colgantes con QR que derive a la galería digital privada.'],
      check: ['Aprobar maqueta editorial de la Bitácora de Saberes', 'Cotizar impresión en papel kraft/reciclado local', 'Diseñar kit de bienvenida en bolsa de lienzo con huso']
    },
    { num: 6, f: '2026-10-14', t: 'ind', lugar: 'Turismo', hora: '11:00 a 13:00', titulo: 'Encuadre regulatorio, seguros y alianzas con agencias receptivas (EVyT)', asistentes: ['mariela', 'leandro'],
      guia: 'Establecer el encuadre normativo del paquete. Definir la contratación de pólizas de seguro de accidentes personales / turismo activo para los excursionistas. Presentar el producto ante agencias receptivas habilitadas (EVyT) de Esquel para operar bajo su paraguas comercial.',
      preguntas: ['¿Qué agencia local está dispuesta a firmar un acuerdo de comercialización exclusiva o co-branding?', '¿Qué requisitos de habilitación de transporte se exigen para los traslados a Nahuelpan y Percy?'],
      objetivos: ['Modelo de convenio marco con agencia receptiva local.', 'Cotización de póliza de seguro de turismo alternativo.', 'Redactar condiciones generales de contratación y políticas de cancelación estricta.'],
      check: ['Redactar modelo de convenio marco con agencias EVyT', 'Cotizar póliza de turismo activo y accidentes personales', 'Definir políticas de reserva, seña y cancelación']
    },
    { num: 7, f: '2026-10-19', t: 'ter', lugar: 'Chacra Rural (Nahuelpan / Percy)', hora: '09:00 a 13:00', titulo: 'Banco de contenidos visuales de autor y ficha comercial B2B', asistentes: ['noelia'],
      guia: 'Producción fotográfica y audiovisual profesional (tomas de detalle en huso, humeado de ollas de tintes con calafate, vellones ovinos, retratos cálidos de Cecilia y anfitrionas campesinas). Redacción final del brochure comercial B2B para comercializadores emisivos de Buenos Aires, Córdoba y Rosario.',
      preguntas: ['¿Qué tomas transmiten paz, textura y autenticidad sin caer en el cliché folclórico?', '¿Qué formato de video vertical funciona mejor para redes de comunidades de tejedoras y clubes de lana?'],
      objetivos: ['Banco de 30 fotografías profesionales de alta calidad.', '3 reels cortos narrados por Cecilia explicando la experiencia.', 'Ficha comercial digital en PDF lista para envío a agencias.'],
      check: ['Rodaje fotográfico y clips en chacra rural', 'Seleccionar y editar 3 reels de autor', 'Diseñar brochure digital B2B para agencias emisivas']
    },
    { num: 8, f: '2026-11-10', t: 'cie', lugar: 'Melipal', hora: '10:00 a 14:00', titulo: 'Validación sensorial piloto, rueda de negocios y cierre de cohorte', asistentes: ['leandro', 'mariela', 'noelia', 'adria', 'francisco', 'agustina', 'cesia'],
      guia: 'Validación final de la experiencia mediante un micro-taller demostrativo en vivo (hilado en huso y muestrario de lanas teñidas en el stand). Participación en la ronda de negocios con operadores turísticos y entrega de la distinción oficial del Laboratorio.',
      preguntas: ['¿Qué agencias de Buenos Aires y de la comarca confirman fechas de preventa para marzo/abril de 2027?', '¿Cómo queda enlazado el retiro en la web oficial esquel.site?'],
      objetivos: ['Montaje del stand de experiencia con vellones, husos, ovillos teñidos y bitácoras.', 'Presentación del pitch de 3 minutos de Cecilia.', 'Firma de al menos 2 cartas de intención con agencias receptivas.'],
      check: ['Montar stand vivencial con huso y muestrario de tintes', 'Presentar pitch de 3 minutos ante prestadores comarcales', 'Firmar al menos 2 cartas de intención con agencias']
    }
  ],
  yamamori: [
    { num: 1, f: '2026-09-17', t: 'ind', lugar: 'Virtual / Esquel LAB', hora: '15:00 a 17:00', titulo: 'Diagnóstico operativo, análisis del piloto de octubre y devolución técnica', asistentes: ['mariela', 'agustina', 'leandro'],
      guia: 'Revisar la grilla operativa del viaje piloto del 30 de octubre. Validar el estado de reservas de combi, hotelería (Sur Sur y Trevelin), La Trochita y el Museo Histórico con Carla. Establecer el protocolo de contingencias.',
      preguntas: ['¿Qué aspecto del itinerario de octubre genera mayor incertidumbre logística?', '¿Cómo coordinamos los traslados del aeropuerto con la combi contratada?', '¿Qué seguro de viaje cubre a los 10 pasajeros?'],
      objetivos: ['Planilla de reservas y vouchers del viaje de octubre completada.', 'Cronograma minuto a minuto de las 4 jornadas.', 'Confirmación de póliza de asistencia médica y seguro de accidentes.'],
      check: ['Revisar planilla de reservas y vouchers de octubre', 'Validar cronograma minuto a minuto con transportista', 'Confirmar póliza de seguro de viaje para los 10 pasajeros']
    },
    { num: 2, f: '2026-09-23', t: 'ind', lugar: 'Virtual / Gabinete Técnico', hora: '14:00 a 16:30', titulo: 'Curaduría de itinerarios desestacionalizados (Otoño de Lenga, Invierno & Sakura)', asistentes: ['mariela', 'agustina'],
      guia: 'Estructurar las fichas técnicas de los dos paquetes desestacionalizados para 2027: 1) "Colores del Otoño & Bosques de Lenga" (marzo/abril); 2) "Ciruelos en Flor & El Sakura Patagónico" (septiembre/octubre). Fijar duraciones de 4 días / 3 noches y cupos cerrados de 8 a 10 pasajeros.',
      preguntas: ['¿Cómo destacamos el florecimiento de ciruelos en la avenida Ameghino como atractivo cultural?', '¿Qué estancias rurales garantizan calidez y gastronomía típica en otoño?'],
      objetivos: ['Redactar ficha descriptiva del paquete Otoño de Lenga.', 'Redactar ficha del paquete Sakura de Esquel.', 'Definir fechas tentativas del calendario 2027.'],
      check: ['Redactar ficha técnica del paquete Otoño de Lenga', 'Redactar ficha del paquete Sakura Patagónico en Ameghino', 'Definir calendario de 4 salidas para 2027']
    },
    { num: 3, f: '2026-09-28', t: 'ind', lugar: 'Turismo / Virtual', hora: '15:00 a 17:30', titulo: 'Articulación territorial y cruces con emprendimientos de la cohorte', asistentes: ['mariela', 'noelia'],
      guia: 'Conectar formalmente a Yamamori con prestadores de la cohorte: provisión de té verde de autor con Sandra Roberts (Haiku), visita a talleres de tornería de madera con Andrés Crova en Alto Río Percy, y coordinación de obsequios artesanales de bienvenida.',
      preguntas: ['¿Qué formato de cata o servicio de té puede ofrecer Haiku a los grupos de Julia?', '¿Cómo organizamos una parada de 45 minutos en el taller de tornería sin entorpecer la producción?'],
      objetivos: ['Acuerdo preliminar de provisión de té con Haiku.', 'Inclusión de la parada en Percy en el dossier comercial.', 'Definición del souvenir de madera de lenga o té patagónico.'],
      check: ['Pautar provisión de té verde con Haiku', 'Programar parada de visita con Los Crovas en Percy', 'Definir recuerdo artesanal de lenga para los grupos']
    },
    { num: 4, f: '2026-09-30', t: 'gru', lugar: 'Célula 4 (Melipal)', hora: '09:30 a 12:30', titulo: 'Costeo B2B, tarifarios netos y márgenes (Célula 4)', asistentes: ['mariela', 'agustina', 'noelia'],
      guia: 'Construir la matriz de costeo integral de los paquetes de autor. Separar costos fijos (transporte privado, coordinación, guías de sitio) y variables (hotelería, comidas, ingresos, seguros), estableciendo la tarifa neta confidencial y el margen de intermediación comercial.',
      preguntas: ['¿Cuál es el costo unitario por pasajero en base doble para grupos de 8 personas?', '¿Qué honorario de coordinación neta percibe Julia?', '¿Qué comisión del 15% al 20% se asigna al canal minorista?'],
      objetivos: ['Planilla Excel de costeo integral parametrizada.', 'Punto de equilibrio fijado en 6 pasajeros.', 'Tarifario cerrado en pesos y en dólares para receptivo internacional.'],
      check: ['Completar planilla de costeo integral parametrizada', 'Fijar punto de equilibrio en 6 pasajeros', 'Definir tarifario neto confidencial y PVP']
    },
    { num: 5, f: '2026-10-07', t: 'ind', lugar: 'Virtual / Gabinete', hora: '14:30 a 16:30', titulo: 'Protocolo de hospitalidad "Omotenashi Patagónico" y kit de bienvenida', asistentes: ['agustina'],
      guia: 'Diseñar el manual de procedimientos de hospitalidad omotenashi adaptado a la Patagonia: recepción en aeropuerto, kit de bienvenida en la habitación (pantuflas artesanales, termo con té verde de Haiku), toallas tibias en excursiones y encuesta de satisfacción sensorial.',
      preguntas: ['¿Qué elementos tangibles componen el kit de bienvenida?', '¿Cómo capacitamos al transportista y al personal del hotel para que respeten las pautas de cortesía japonesa?'],
      objetivos: ['Documento del protocolo Omotenashi redactado.', 'Presupuesto del kit de bienvenida por pasajero.', 'Modelo de encuesta de satisfacción post-viaje en español e inglés/japonés.'],
      check: ['Redactar manual de protocolo Omotenashi', 'Presupuestar kit de bienvenida por pasajero', 'Diseñar encuesta de satisfacción sensorial post-viaje']
    },
    { num: 6, f: '2026-10-15', t: 'ind', lugar: 'Turismo', hora: '11:00 a 13:00', titulo: 'Encuadre legal, seguros y convenio marco con agencia receptiva (EVyT)', asistentes: ['mariela', 'leandro'],
      guia: 'Redactar y suscribir el modelo de convenio marco de cooperación comercial entre Yamamori Travel y una Agencia de Viajes Receptiva (EVyT) habilitada de Esquel. Delimitar responsabilidades legales, cobertura de seguros, facturación y cobro de señas.',
      preguntas: ['¿Qué agencia local asume la responsabilidad técnica y emisión de vouchers?', '¿Cómo se gestionan las pólizas de seguro de turismo alternativo y accidentes personales?'],
      objetivos: ['Borrador del convenio marco con agencia EVyT local.', 'Protocolo de contratación de pólizas de asistencia médica.', 'Políticas de reserva, pago anticipado y cancelación estricta.'],
      check: ['Redactar modelo de convenio marco con agencia EVyT', 'Establecer protocolo de seguros y asistencia al viajero', 'Definir políticas de cobro, señas y cancelación']
    },
    { num: 7, f: '2026-10-26', t: 'ind', lugar: 'Virtual / Gabinete', hora: '15:00 a 17:00', titulo: 'Estrategia de comunicación bilingüe, Instagram de autor y difusión', asistentes: ['agustina'],
      guia: 'Optimizar el perfil de Instagram @yamamoritravel con narrativa bilingüe (español/japonés). Diseñar el brochure digital comercial en PDF con estética minimalista japonesa, destacando las bondades de Esquel como destino seguro, puro y exclusivo.',
      preguntas: ['¿Cómo explicamos el concepto de Yamamori y Omotenashi en las redes sin perder cercanía con el público argentino?', '¿Qué material de registro se tomará durante el viaje piloto de octubre?'],
      objetivos: ['Bio de Instagram optimizada con enlace directo a WhatsApp y brochure.', 'Ficha técnica comercial digital en PDF lista para agencias.', 'Pauta de registro fotográfico y video durante el viaje del 30/10.'],
      check: ['Optimizar biografía y feed bilingüe en Instagram', 'Maquetar brochure digital comercial en PDF', 'Definir pauta de cobertura fotográfica para el viaje del 30/10']
    },
    { num: 8, f: '2026-11-10', t: 'cie', lugar: 'Melipal', hora: '10:00 a 14:00', titulo: 'Balance del viaje piloto, ronda de negocios y cierre de cohorte', asistentes: ['leandro', 'mariela', 'agustina', 'noelia', 'adria', 'francisco', 'cesia'],
      guia: 'Presentar las conclusiones y métricas reales del viaje piloto ejecutado del 30 de octubre al 2 de noviembre. Participar en la rueda de negocios con el sector hotelero y agencias receptivas de Esquel, presentando el calendario oficial de salidas 2027.',
      preguntas: ['¿Qué devoluciones dieron los 10 pasajeros del viaje de octubre?', '¿Qué ajustes operativos requiere el paquete para la temporada 2027?', '¿Qué alianzas comerciales quedan selladas?'],
      objetivos: ['Informe de balance y evaluación de satisfacción del viaje piloto.', 'Presentación del calendario de 4 salidas para 2027.', 'Firma de convenios comerciales con prestadores locales.'],
      check: ['Presentar informe de balance y métricas del viaje piloto', 'Exponer calendario 2027 en la rueda de negocios', 'Firmar al menos 2 acuerdos comerciales con prestadores locales']
    }
  ],
  porota: [
    { num: 1, f: '2026-09-17', t: 'ter', lugar: 'Casa de Porota (Don Bosco 920)', hora: '16:30 a 18:30', titulo: 'Diagnóstico en Don Bosco 920, relevamiento de las 7.552 piezas y espacio físico', asistentes: ['adria', 'francisco'],
      guia: 'Visita al domicilio de Pelusa en Don Bosco 920. Inspección ocular de la sala temática de acceso, comedor, cocina, jardín delantero y sanitarios. Relevamiento de las 7.552 piezas de la Colección Arqueológica Clara Rosa Garín ya inventariadas por los antropólogos Heidi y Leandro. Relevamiento del mobiliario existente a adaptar (escritorio antiguo, vitrina de pared y mesas de té).',
      preguntas: ['¿Qué capacidad máxima de comensales sentados permite el salón y comedor para que la experiencia sea cómoda e íntima?', '¿Cómo protegemos las piezas más delicadas (puntas de flecha milimétricas) del contacto directo de los visitantes?', '¿Qué disponibilidad de turnos fijamos por la tarde para no interferir con las mañanas de Newcom de Pelusa?'],
      objetivos: ['Mapear el circuito físico de la visita: recepción en jardín, sala arqueológica y salón merendero.', 'Relevar las medidas del mobiliario para cotizar las tapas de vidrio templado de seguridad.', 'Definir el cronograma semanal de trabajo conjunto y días fijos de atención: martes, jueves y sábados.'],
      check: ['Inspeccionar sala temática, comedor, jardín y sanitarios en Don Bosco 920', 'Inventariar el mobiliario a intervenir con vidrio templado de seguridad', 'Establecer los días y horarios fijos semanales para las reuniones de consultoría y visitas']
    },
    { num: 2, f: '2026-09-24', t: 'ter', lugar: 'Don Bosco 920', hora: '16:30 a 18:30', titulo: 'Curaduría del guion emocional de Porota, vitrinas y mesas con vidrio de seguridad', asistentes: ['adria', 'francisco'],
      guia: 'Estructurar el guion narrativo y testimonial de Alda ("Pelusa"): la historia de los pioneros de Tecka (1875), la vida en Estancia La Central, las anécdotas de Porota recorriendo el campo a caballo y el rescate de las piezas líticas. Definir con el museólogo y vecino Marcelo Troiano las especificaciones técnicas de las cajas vidriadas y vitrinas para garantizar la conservación patrimonial.',
      preguntas: ['¿Cuáles son las anécdotas más conmovedoras de Porota a caballo que conectan emocionalmente con el visitante?', '¿Qué piezas líticas seleccionamos como muestra estrella para el recorrido de 40 minutos?', '¿Cómo organizamos la iluminación puntual y fichas técnicas explicativas en cada mueble?'],
      objetivos: ['Redactar el guion didáctico y emocional paso a paso de la experiencia turística.', 'Aprobar las especificaciones técnicas de vidrios templados para el escritorio antiguo y mesas.', 'Elaborar las fichas interpretativas de las 30 piezas arqueológicas más representativas.'],
      check: ['Aprobar el borrador del guion narrativo de Alda para la bienvenida y exposición', 'Definir el presupuesto de vidrios templados y herrajes para el mobiliario', 'Validar la selección de piezas con el marco de custodia legal de Patrimonio Cultural']
    },
    { num: 3, f: '2026-10-01', t: 'ind', lugar: 'Melipal / Espacio LAB', hora: '16:30 a 18:30', titulo: 'Estructura de costos, menú de la merienda de campo y tarifario dual B2C/B2B', asistentes: ['adria', 'francisco'],
      guia: 'Construir la planilla de costeo paramétrico de la merienda casera (harina, manteca de campo, mermeladas regionales, variedades de té en hebras, café y leña/gas). Fijar la tarifa minorista por persona y un esquema dual (turistas vs residentes comarcales). Establecer comisiones del 20-25% para agencias de viajes receptivas y conserjerías de hoteles boutique de Esquel y Trevelin.',
      preguntas: ['¿Cuál es el costo unitario de elaboración de la merienda y cuál es el punto de equilibrio mínimo por grupo?', '¿Qué precio por cubierto resulta competitivo frente a las casas de té tradicionales de la comarca?', '¿Qué política de reservas previas (seña del 50%) implementaremos para evitar cancelaciones?'],
      objetivos: ['Parametrizar los costos fijos y variables de la merienda campestre.', 'Emitir la lista oficial de precios: público general, tarifa social residente y tarifa mayorista B2B.', 'Diseñar el protocolo de reservas anticipadas y cancelaciones con 24 hs de margen.'],
      check: ['Cargar la planilla de costos y márgenes de rentabilidad en Drive/Excel', 'Emitir la ficha tarifaria comercial para agencias y hoteles boutique', 'Establecer el menú fijo estacional de la merienda de campo']
    },
    { num: 4, f: '2026-10-08', t: 'ind', lugar: 'Melipal / Espacio LAB', hora: '16:30 a 18:30', titulo: 'Desarrollo de marca "Lo de Porota", souvenirs de autor y encuadre bromatológico', asistentes: ['adria', 'francisco'],
      guia: 'Diseñar la identidad visual del emprendimiento: logotipo evocativo de "Lo de Porota", paleta cromática de tierra y piedra, tipografía rústica y elegante, y placa discreta para el portón de entrada. Prototipar el souvenir de autor: cofre rústico con 2 réplicas artesanales de puntas de flecha en arcilla/madera y fascículo biográfico "Memorias de Porota Garín". Revisión del trámite bromatológico simplificado.',
      preguntas: ['¿Qué elementos gráficos sintetizan mejor la unión entre la arqueología lítica y el calor de la merienda hogareña?', '¿Qué costo de producción tienen los cofres de réplicas para asegurar un margen sustentable?', '¿Qué requisitos bromatológicos municipales deben verificarse en la cocina de Don Bosco 920?'],
      objetivos: ['Aprobar la identidad visual y manual básico de marca de "Lo de Porota".', 'Cotizar y prototipar la primera serie de 50 cofres de souvenirs con artesanos locales.', 'Formalizar el cumplimiento de normas de manipulación segura de alimentos.'],
      check: ['Aprobar el logotipo oficial, paleta de colores y cartel de bienvenida exterior', 'Validar el prototipo del cofre con réplicas de puntas de flecha y cuadernillo', 'Completar el checklist de higiene y bromatología en el salón merendero']
    },
    { num: 5, f: '2026-10-15', t: 'ter', lugar: 'Don Bosco 920', hora: '16:00 a 18:30', titulo: 'Producción audiovisual profesional 4K, tomas del jardín y canal de reservas web', asistentes: ['adria', 'francisco', 'leandro'],
      guia: 'Jornada de producción fotográfica y audiovisual profesional en Don Bosco 920. Registro en video 4K de Alda en la cocina amasando pan, tomas macro de las piezas arqueológicas protegidas bajo vidrio, retratos de Porota y planos del jardín florido. Configuración de la landing page transaccional desarrollada por Leandro Choi en el ecosistema digital de Esquel, con calendario de turnos y WhatsApp Business.',
      preguntas: ['¿Qué encuadres y secuencias transmiten con mayor fuerza la ternura de Pelusa y el misterio milenario de las piedras?', '¿Cómo estructurar la reserva online para que el turista seleccione turnos de 6 a 10 personas sin confusiones?', '¿Qué enlace de Mercado Pago / transferencia dejamos asociado a la seña de confirmación?'],
      objetivos: ['Generar un banco de 30 fotografías profesionales de alta calidad y clips para reels.', 'Publicar la landing page de reservas de "Lo de Porota" con pasarela de pagos.', 'Capacitar a Pelusa y su familia en la confirmación de turnos por WhatsApp.'],
      check: ['Completar la sesión de fotos 4K y registro en video de la anfitriona y la colección', 'Aprobar la interfaz y textos de la landing page de reservas online', 'Cargar el catálogo de servicios con precios y horarios en WhatsApp Business']
    },
    { num: 6, f: '2026-10-22', t: 'ind', lugar: 'Secretaría de Turismo / Melipal', hora: '16:30 a 18:30', titulo: 'Rueda comercial B2B con agencias receptivas, hoteles boutique y Marcelo Troiano', asistentes: ['adria', 'francisco'],
      guia: 'Presentar formalmente la propuesta a las principales agencias de turismo receptivo de Esquel y Trevelin, destacando su exclusividad como experiencia de cupo reducido. Coordinar alianzas con hoteles boutique céntricos para recomendar la merienda a huéspedes que buscan vivencias culturales no masivas. Articular con Marcelo Troiano (El Arroyo Que Nos Ve Crecer...) un circuito combinado de memoria urbana y merienda.',
      preguntas: ['¿Qué receptividad muestran los hoteles boutique en derivar pasajeros para una merienda privada y cultural?', '¿Cómo articulamos con las agencias receptivas la venta anticipada de grupos exclusivos?', '¿Qué sinergias de promoción cruzada podemos implementar con los demás proyectos de la cohorte?'],
      objetivos: ['Presentar el catálogo oficial a 3 agencias de viajes receptivas y 3 hoteles boutique de Esquel.', 'Firmar al menos 2 acuerdos comerciales de derivación turística con comisiones formalizadas.', 'Diseñar un itinerario conjunto optativo con Marcelo Troiano (visita histórica + merienda).'],
      check: ['Reunión comercial con agencias y alojamientos seleccionados con muestras de té y souvenirs', 'Firmar convenios de derivación turística con tarifario mayorista', 'Coordinar la primera salida combinada piloto con Marcelo Troiano']
    },
    { num: 7, f: '2026-10-29', t: 'ter', lugar: 'Don Bosco 920', hora: '16:30 a 19:00', titulo: 'Simulacro vivencial de merienda (Fam Tour con informantes turísticos y guías)', asistentes: ['adria', 'francisco', 'leandro'],
      guia: 'Simulacro real en Don Bosco 920 con un grupo de prueba (8 informantes de las Secretarías de Turismo de Esquel y Trevelin, guías del Parque y prestadores de la cohorte). Recorrido completo: bienvenida en el jardín, relato biográfico de Porota, exhibición de piezas en el escritorio vidriado, servicio de merienda casera caliente, entrega de souvenirs y testeo del sistema de reservas.',
      preguntas: ['¿Cómo evaluaron los participantes la fluidez del guion y la calidez del trato de Alda?', '¿La disposición del mobiliario y los vidrios de protección garantizaron la seguridad total de las piezas?', '¿Qué sugerencias de mejora gastronómica o de ambientación plantearon los informantes turísticos?'],
      objetivos: ['Validar los tiempos operativos reales de la merienda y exposición (2 horas exactas).', 'Consolidar la soltura y confianza de Alda frente al público en su propio hogar.', 'Recoger devoluciones técnicas mediante encuestas para perfeccionar el servicio antes del debut comercial.'],
      check: ['Ejecutar el Fam Tour con 8 participantes del sector turístico comarcal', 'Completar las encuestas de evaluación sobre hospitalidad, guion y degustación', 'Ajustar detalles finales del servicio de mesa y cartelería informativa']
    },
    { num: 8, f: '2026-11-05', t: 'col', lugar: 'Melipal / Espacio LAB', hora: '09:30 a 13:00', titulo: 'Ronda de negocios final, pitch institucional y homenaje público al legado de Porota', asistentes: ['adria', 'francisco', 'leandro'],
      guia: 'Participación protagónica en el Encuentro de Cierre de Esquel LAB en el Centro Cultural Melipal. Montaje de mesa temática de "Lo de Porota" con fotografías históricas de Tecka, piezas representativas en vitrina cerrada, muestras del cofre de souvenirs y degustación de tortas fritas caseras. Pitch institucional de 3 minutos de Alda Inés Mateo Garín. Firma de acuerdos comerciales y entrega del plan operativo post-LAB.',
      preguntas: ['¿Qué balance arrojan las primeras reservas turísticas ingresadas a través de la web y agencias?', '¿Cómo se organizará el calendario operativo de Pelusa para la temporada alta de verano 2026/2027?', '¿Qué pasos siguen para la gestión de fondos de mejora edilicia ante Cultura o Municipio?'],
      objetivos: ['Exhibir el producto consolidado ante autoridades municipales, prestadores y medios de comunicación.', 'Consolidar convenios comerciales firmes con el sector privado para el verano.', 'Entregar el informe final de consultoría técnica y hoja de ruta post-incubación.'],
      check: ['Montar stand interactivo con vitrina de piezas, souvenirs y degustación campesina', 'Exponer el pitch de 3 minutos de Alda ante autoridades y empresarios turísticos', 'Firmar acuerdos comerciales formales y entrega de la hoja de ruta 2026/2027']
    }
  ],
  margher: [
    { num: 1, f: '2026-09-19', t: 'ter', lugar: 'Establecimiento Margherita (Ladera Sur del Nahuelpan)', hora: '10:00 a 13:00', titulo: 'Diagnóstico integral en territorio: casita centenaria, colmenares y corrales', asistentes: ['adria', 'noelia'],
      guia: 'Visita a las 500 ha en la ladera sur del Cerro Nahuelpan (a 18 km de Esquel, límite con Trevelin). Inspección ocular de la casita centenaria de adobe y tejuelas de alerce, galería cubierta, sector de apiarios y potreros de asnos y ovinos de cabaña. Mapeo de senderos pedestres internos y evaluación del estado de accesos viales.',
      preguntas: ['¿Qué capacidad máxima de carga simultánea toleran las instalaciones para mantener la intimidad campestre?', '¿Cómo organizamos el calendario para no entorpecer los ciclos reproductivos ovinos y de cosecha apícola?', '¿Qué mejoras menores de cartelería rústica y delimitación de senderos se requieren en el predio?'],
      objetivos: ['Mapear el circuito físico: recepción en galería, senderos hacia apiarios, potreros de asnos y miradores.', 'Definir el modelo de negocio: agroturismo de día y eventos especiales, preservando la casita como refugio estacional.', 'Acordar el cronograma semanal de consultoría y roles operativos de Dante y su compañera.'],
      check: ['Inspeccionar casita de adobe, galería, apiario y corrales en la ladera sur', 'Relevar el estado de accesos viales y conectividad para los visitantes', 'Fijar el cronograma de 8 encuentros de consultoría técnica']
    },
    { num: 2, f: '2026-09-26', t: 'ter', lugar: 'Establecimiento Margherita', hora: '10:30 a 13:00', titulo: 'Estandarización del Día de Campo, guion de apicultura y protocolo con asnos', asistentes: ['adria', 'noelia'],
      guia: 'Cerrar la estructura pedagógica y el guion narrativo de la experiencia "Día de Campo Margherita" (3h 30min). Redactar las pautas de seguridad e higiene para la demostración apícola (uso de ahumador, trajes y cata de mieles), la interacción con los burritos mansos y el taller de amasado de tortas fritas en la galería. Fijación de cupos máximos (10 personas por turno).',
      preguntas: ['¿Cuáles son los pasos clave del guion para que Dante transmita su pasión apícola de forma didáctica y segura?', '¿Cómo articulamos el amasado participativo con la anfitriona mientras se preparan las brasas en la parrilla?', '¿Qué elementos de protección y bioseguridad se dispondrán para los visitantes en el colmenar?'],
      objetivos: ['Redactar la ficha técnica y guion interpretativo paso a paso del Día de Campo.', 'Formalizar el protocolo de seguridad apícola y deslinde de responsabilidad civil en turismo rural.', 'Ensayar la dinámica de recepción con mate y tortas fritas calientes en la galería.'],
      check: ['Aprobar el guion didáctico del Día de Campo y la demostración apícola', 'Verificar stock y condiciones de trajes apícolas y caretas para visitantes', 'Fijar la capacidad de carga (máx. 10 personas) y política de reservas previas de 24 hs']
    },
    { num: 3, f: '2026-10-03', t: 'ind', lugar: 'Melipal / Espacio LAB', hora: '10:30 a 12:30', titulo: 'Estructura de costos paramétricos, punto de equilibrio y tarifario dual B2C/B2B', asistentes: ['adria', 'noelia'],
      guia: 'Construir la planilla paramétrica de costos de las experiencias (ingredientes gastronómicos, leña de monte, mantenimiento, seguros de turismo rural y honorarios). Establecer el "Precio Único Oficial" del establecimiento y estructurar el margen del 20% de comisión para agencias receptivas y conserjerías de hoteles de Esquel y Trevelin.',
      preguntas: ['¿Cuál es el costo unitario por visitante y cuál es el punto de equilibrio mínimo para abrir la tranquera?', '¿Cómo aseguramos que las agencias vendan al mismo precio oficial sin recargos abusivos?', '¿Qué porcentaje de seña anticipada (50%) exigirá la plataforma web para congelar la reserva?'],
      objetivos: ['Parametrizar los costos fijos y variables de la media jornada y jornada entera.', 'Emitir la lista oficial de precios: público general, tarifa social residente y tarifa mayorista B2B.', 'Diseñar la política de reservas, cancelaciones y reprogramaciones climáticas.'],
      check: ['Cargar la planilla de costos y márgenes de rentabilidad en Drive/Excel', 'Emitir el tarifario oficial unificado para agencias y hoteles boutique', 'Definir el protocolo de contingencia climática en caso de lluvias intensas']
    },
    { num: 4, f: '2026-10-10', t: 'ter', lugar: 'Establecimiento Margherita', hora: '10:00 a 13:00', titulo: 'Preparación operativa de la Fiesta de la Esquila y logística gastronómica', asistentes: ['adria', 'noelia'],
      guia: 'Planificar minuciosamente la realización del hito anual "Fiesta de la Esquila & Asado de Cordero Patagónico" en coincidencia con la temporada alta de Tulipanes en Trevelin. Coordinar la demostración en vivo de comparsa de esquila, clasificación de vellones, preparación del cordero al asador a la leña y venta de frascos de miel "Oro de Esquel" con packaging rústico.',
      preguntas: ['¿Qué logística de compra y ovinos seleccionados se dispondrán para la jornada de esquila?', '¿Cómo se ambientará la galería y el patio para recibir cómodamente a los 15 comensales?', '¿Qué stock de frascos de miel fraccionada en CAPEC se destinará para venta en origen?'],
      objetivos: ['Cerrar el cronograma minuto a minuto de la jornada de esquila y banquete.', 'Verificar la logística del fuego, asadores, vajilla campesina y puestos de sombra.', 'Diseñar la presentación rústica de los frascos de miel como souvenir conector.'],
      check: ['Aprobar el programa operativo de la Fiesta de la Esquila', 'Coordinar con la comparsa de esquila y el asador el horario de demostración', 'Disponer el stock de miel Oro de Esquel con la nueva etiqueta institucional']
    },
    { num: 5, f: '2026-10-17', t: 'ter', lugar: 'Establecimiento Margherita', hora: '10:00 a 13:30', titulo: 'Producción audiovisual 4K con dron y arquitectura de la web transaccional', asistentes: ['adria', 'noelia', 'leandro'],
      guia: 'Jornada de producción fotográfica y audiovisual profesional financiada por el LAB (cámaras 4K y dron en la ladera sur del Nahuelpan). Registro aéreo de las 500 ha, tomas de Dante en el apiario con traje y ahumador con las cumbres de fondo, casita de adobe y tejuelas, burritos mansos y cocina al fuego. En paralelo, revisión técnica con Leandro Choi de la web transaccional y pasarela de reservas.',
      preguntas: ['¿Cuáles son los ángulos aéreos más espectaculares de la ladera sur y la casita centenaria?', '¿Cómo estructurar la carga de datos del visitante en la web para automatizar la confirmación de turnos?', '¿Qué pasarela de pagos (Mercado Pago / transferencia) dejamos integrada para cobrar las señas?'],
      objetivos: ['Completar el plan de rodaje con cámaras 4K y tomas de dron para cápsulas institucionales.', 'Validar la interfaz y textos de la plataforma web desarrollada ad-honorem por Leandro.', 'Capacitar a Dante y su compañera en la administración sencilla del panel de reservas.'],
      check: ['Rodar el material audiovisual 4K y banco de 30 fotografías profesionales', 'Aprobar el diseño y catálogo de la plataforma web transaccional', 'Configurar el canal de WhatsApp Business con respuestas rápidas y catálogo']
    },
    { num: 6, f: '2026-10-24', t: 'ind', lugar: 'Secretaría de Turismo / Melipal', hora: '10:30 a 12:30', titulo: 'Rueda comercial con agencias receptivas, hoteleros y sinergias de cohorte', asistentes: ['adria', 'noelia'],
      guia: 'Presentar formalmente la propuesta a las 4 agencias de turismo receptivo habilitadas de Esquel y Trevelin y conserjerías de hoteles boutique. Articular sinergias ecosistémicas: provisión de miel para el productor de hidromiel de la comarca, circuitos de cicloturismo de estancia con Corcho Bikes y meriendas cruzadas con Haiku Casa de Té.',
      preguntas: ['¿Qué interés manifiestan las agencias en comercializar el Día de Campo y la Fiesta de la Esquila con comisión del 20%?', '¿Cómo articulamos la logística de traslados en combis o 4x4 para turistas sin movilidad propia?', '¿Qué acuerdos de provisión de miel y cera pura establecemos con emprendimientos de la cohorte?'],
      objetivos: ['Reunirse con 3 agencias de viaje receptivas y 3 hoteles boutique de Esquel y Trevelin.', 'Firmar al menos 2 convenios comerciales de derivación turística con comisiones formalizadas.', 'Acordar una alianza de provisión de miel con el elaborador comarcal de hidromiel.'],
      check: ['Presentar el catálogo oficial a agencias receptivas y prestadores hoteleros', 'Firmar acuerdos de comercialización turística con tarifario mayorista unificado', 'Concretar la alianza estratégica de miel para hidromiel y cicloturismo']
    },
    { num: 7, f: '2026-10-31', t: 'ter', lugar: 'Establecimiento Margherita', hora: '11:00 a 14:30', titulo: 'Fam Tour vivencial con agencias receptivas, hoteleros y test de reservas web', asistentes: ['adria', 'noelia', 'leandro'],
      guia: 'Simulacro vivencial real en el campo con 10 invitados estratégicos (titulares de agencias receptivas, recepcionistas de hoteles boutique y prensa turística). Prueba en vivo de la experiencia: recepción con mate y tortas fritas, visita al apiario y potrero de asnos, pizzas a la parrilla en la galería, degustación de miel y testeo del circuito de reservas web.',
      preguntas: ['¿Cómo evaluaron los operadores turísticos el dinamismo del guion y la calidez del anfitrión?', '¿La duración de 3 horas y media resultó equilibrada y atractiva para la venta en mostrador?', '¿Qué sugerencias técnicas plantearon los recepcionistas de hotel para recomendar el lugar?'],
      objetivos: ['Validar los tiempos operativos reales de la experiencia completa en condiciones simuladas.', 'Fidelizar a los recepcionistas y agentes de viaje como promotores directos del establecimiento.', 'Recoger encuestas de satisfacción para pulir detalles del servicio antes de la apertura estival.'],
      check: ['Ejecutar el Fam Tour con 10 participantes del sector turístico comarcal', 'Completar encuestas de evaluación sobre hospitalidad, guion y gastronomía', 'Ajustar detalles finales del servicio de mesa y cartelería informativa interna']
    },
    { num: 8, f: '2026-11-07', t: 'col', lugar: 'Melipal / Espacio LAB', hora: '09:30 a 13:00', titulo: 'Ronda de negocios final, lanzamiento de la web y hoja de ruta post-LAB', asistentes: ['adria', 'noelia', 'leandro'],
      guia: 'Participación protagónica en el Encuentro de Cierre de Esquel LAB en el Centro Cultural Melipal. Montaje de mesa temática con frascos de miel Oro de Esquel, velas de cera, vellones de lana y fotos 4K del Nahuelpan. Presentación pública de la plataforma web transaccional. Pitch de 3 minutos de Dante Oliva y compañera ante autoridades y empresarios turísticos. Entrega del informe final de consultoría técnica.',
      preguntas: ['¿Cuáles son las reservas confirmadas en la plataforma web para la temporada de verano?', '¿Cómo se organizará el calendario de Dante entre la zafra apícola y los días de campo turísticos?', '¿Qué pasos inmediatos se proyectan para el desarrollo del SUM rústico modular a futuro?'],
      objetivos: ['Presentar públicamente la oferta consolidada de Agroturismo de Establecimiento Margherita.', 'Consolidar acuerdos comerciales firmes con el sector receptivo comarcal para el verano.', 'Entregar el informe final de consultoría técnica y cronograma de temporada alta 2026/2027.'],
      check: ['Montar stand interactivo con degustación de miel, fotos 4K y terminal web', 'Exponer el pitch de 3 minutos ante autoridades municipales, prestadores y medios', 'Firmar acuerdos comerciales formales y entrega de la hoja de ruta post-incubación']
    }
  ],
  sabor: [
    { num: 1, f: '2026-09-18', t: 'ter', lugar: 'Casa de Piedra (Comunidad Nahuelpan)', hora: '10:00 a 12:30', titulo: 'Diagnóstico integral en Casa de Piedra, sendero Huella del Cóndor y sanitarios', asistentes: ['adria', 'francisco'],
      guia: 'Visita a la Comunidad Nahuelpan. Inspección ocular de Casa de Piedra (antigua escuela comunitaria recuperada), sector de cocina, corrales de gallinas y sendero La Huella del Cóndor. Relevamiento técnico del estado de los baños: verificación de la cámara séptica y pozo ciego ya ejecutados, y detalle de los materiales faltantes (cerámicos, artefactos, grifería y pintura) para su terminación.',
      preguntas: ['¿Cuáles son los costos y tiempos exactos para finalizar los baños antes del inicio de la temporada alta?', '¿Cómo delimitamos el sector de corrales avícolas con pallets y postes para que el turista observe sin invadir la zona productiva?', '¿Qué disponibilidad horaria tienen Lauriano y Yanina para establecer días y turnos fijos de recepción turística?'],
      objetivos: ['Mapear el circuito peatonal: recepción en Casa de Piedra, ascenso por La Huella del Cóndor y regreso al salón.', 'Elaborar el cómputo de materiales y presupuesto necesario para la terminación de los sanitarios.', 'Definir el cronograma semanal de trabajo conjunto y roles de Lauriano y Yanina.'],
      check: ['Inspeccionar Casa de Piedra, cocina, corrales avícolas y sendero', 'Relevar estado de obra civil de los sanitarios y confeccionar listado de materiales faltantes', 'Acordar el calendario de las 8 semanas de incubación con el equipo de Célula 3']
    },
    { num: 2, f: '2026-09-25', t: 'ter', lugar: 'Casa de Piedra (Comunidad Nahuelpan)', hora: '10:30 a 12:30', titulo: 'Estandarización del taller de amasado, protocolo de bienestar animal y guion del agua', asistentes: ['adria', 'francisco'],
      guia: 'Cerrar la estructura pedagógica de la experiencia "Sendero La Huella del Cóndor & Taller de Amasado Comunitario". Empoderar a Nora Yanina Nahuelpan en la conducción del momento de panificados. Formalizar el protocolo de bienestar animal (erradicación total de prácticas invasivas como descolada o castraciones, priorizando la observación de la Gallina Mapuche y la incubadora comunitaria de 1.000 huevos). Redactar el guion interpretativo de la vertiente y el ritual de respeto al agua.',
      preguntas: ['¿Cómo estructuramos el taller de amasado de Yanina para que sea dinámico, participativo y se integre con los tiempos del leudado y horneado?', '¿Qué reglas de interacción y distanciamiento con las aves de corral se explicarán a los visitantes antes de entrar al sendero?', '¿Cómo transmitir el significado ancestral del ritual del agua de forma pedagógica y respetuosa para todo público?'],
      objetivos: ['Redactar la ficha técnica y guion paso a paso de la experiencia del sendero y amasado.', 'Aprobar el protocolo de bienestar animal y pautas de visita responsable a corrales.', 'Ensayar con Yanina la bienvenida y el taller participativo de masas campesinas.'],
      check: ['Validar el guion didáctico del taller de panificados con Yanina', 'Redactar el protocolo de bienestar animal y reglas de comportamiento para visitantes', 'Fijar el cupo máximo por turno (hasta 15 personas) para preservar la intimidad comunitaria']
    },
    { num: 3, f: '2026-10-02', t: 'ind', lugar: 'Oficinas LAB / Secretaría de Turismo de Esquel', hora: '10:30 a 12:30', titulo: 'Costeo paramétrico, tarifario dual B2C/B2B y expediente de subsidio sanitario', asistentes: ['adria', 'francisco'],
      guia: 'Construir la planilla paramétrica de costos de las experiencias y productos. Desglosar insumos de panificación, leña seca, mantenimiento de Casa de Piedra y honorarios de guías/cocineros. Establecer un esquema tarifario dual: tarifa general para turistas y tarifa social comunitaria para residentes de Esquel y Trevelin, más 20-25% de comisión para agencias EVyT. Formulación técnica del pedido de financiamiento ante Producción/CAPEC (Paula Botto) para los sanitarios.',
      preguntas: ['¿Cuál es el costo unitario por visitante en la merienda campesina y cuál es el punto de equilibrio mínimo por grupo?', '¿Qué precios fijamos para las agencias de turismo receptivo para que resulte un producto atractivo en sus paquetes?', '¿Qué línea de crédito o subsidio municipal/provincial se adapta mejor a la culminación de los baños?'],
      objetivos: ['Parametrizar los costos operativos y punto de equilibrio de la merienda y el sendero.', 'Emitir la lista oficial de precios: público general, residentes comarcales y agencias de viaje.', 'Cerrar el documento de solicitud de fondos para la terminación de la obra sanitaria.'],
      check: ['Cargar la planilla de costos y márgenes de rentabilidad en Drive/Excel', 'Emitir el tarifario oficial minorista y mayorista (B2C y B2B)', 'Presentar el expediente de apoyo financiero ante la Secretaría de Producción']
    },
    { num: 4, f: '2026-10-09', t: 'ind', lugar: 'Oficinas LAB / Melipal', hora: '10:30 a 12:30', titulo: 'Logística de ramas de Maqui (Parques Nacionales), Curanto mensual y tazas identitarias', asistentes: ['adria', 'francisco'],
      guia: 'Hacer seguimiento de la nota oficial enviada a Parques Nacionales por indicación del Intendente Matías Taccetta autorizando al poblador Paulo Rosales la extracción sustentable de 5 m³ de ramas de maqui en Los Alerces. Protocolizar el manual operativo y de seguridad del "Curanto de Nahuelpan" (fuego, piedras calientes, técnica de tapado y tiempos de cocción). Cotizar y encargar la primera partida de tazas enlosadas con el logo institucional de Sabor Mapuche para las meriendas.',
      preguntas: ['¿Cuál es el estado de la autorización de Parques Nacionales y la coordinación logística con Paulo Rosales?', '¿Qué calendario fijo mensual de curantos se establecerá para generar previsibilidad en agencias y público?', '¿Qué costo y plazos de entrega maneja el proveedor de las tazas enlosadas serigrafiadas?'],
      objetivos: ['Monitorear la resolución administrativa de Parques Nacionales sobre el maqui.', 'Estandarizar la receta, gramaje y procedimiento del Curanto al Hoyo de Nahuelpan.', 'Aprobar el diseño y mandar a producción la primera tirada de tazas de losa institucionales.'],
      check: ['Verificar avance del permiso de recolección de maqui con la Intendencia de Parques', 'Redactar el manual técnico y de bioseguridad del Curanto tradicional', 'Aprobar la muestra gráfica de las tazas enlosadas serigrafiadas']
    },
    { num: 5, f: '2026-10-16', t: 'ter', lugar: 'Casa de Piedra y Sendero Huella del Cóndor', hora: '10:00 a 13:00', titulo: 'Producción audiovisual de microvideos (15s), fotografía 4K y arquitectura web', asistentes: ['adria', 'francisco', 'leandro'],
      guia: 'Jornada de producción de contenidos en Casa de Piedra y sendero. Rodaje de una serie de 5 microvideos dinámicos de 15 segundos para reels e Instagram (técnica de calentamiento de piedras, encendido del fuego, atado de cordero, amasado participativo con Yanina y ritual en la vertiente). Sesión de 30 fotos 4K de alta calidad de productos gourmet, huevos celestes y paisaje. Revisión técnica con Leandro Choi de la arquitectura de la plataforma web sabormapuche.com.ar.',
      preguntas: ['¿Qué tomas y detalles transmiten con mayor autenticidad la mística de la cocina a leña y la calidez del hogar?', '¿Cómo organizamos la tienda web para despachar sales, merkén y alfajores a nivel nacional?', '¿Qué pasarela de pagos y sistema de confirmación de turnos dejamos integrado en la plataforma web?'],
      objetivos: ['Completar el plan de rodaje de los 5 microvideos y banco de 30 imágenes profesionales.', 'Validar la interfaz y navegación de la plataforma web desarrollada por Leandro Choi.', 'Entrenar a Lauriano y Yanina en la administración del panel de control de reservas y tienda online.'] ,
      check: ['Rodar los 5 microvideos de 15 segundos y seleccionar las 15 mejores fotografías 4K', 'Aprobar la estructura funcional y catálogo de la plataforma web sabormapuche.com.ar', 'Configurar el botón de pago y canal directo de WhatsApp Business']
    },
    { num: 6, f: '2026-10-23', t: 'ind', lugar: 'Secretaría de Turismo / Melipal', hora: '10:30 a 12:30', titulo: 'Rueda comercial B2B con agencias receptivas, La Trochita y sinergias de cohorte', asistentes: ['adria', 'francisco'],
      guia: 'Presentar formalmente el catálogo de experiencias y tarifario a tres agencias receptivas habilitadas de Esquel y Trevelin. Coordinar la articulación operativa con los servicios regulares del Viejo Expreso Patagónico La Trochita para recibir contingentes en Casa de Piedra tras el arribo a la estación Nahuelpan. Planificar sinergias de cohorte: articulación de circuitos culturales con "El Arroyo Que Nos Ve Crecer..." (Marcelo Troiano) y meriendas campesinas combinadas.',
      preguntas: ['¿Qué interés y receptividad muestran las agencias en ofrecer el curanto mensual y el sendero como excursión fija?', '¿Cómo coordinamos los traslados desde la estación del tren hacia Casa de Piedra en los días de viaje?', '¿Qué acuerdos de reciprocidad podemos establecer con otros prestadores de la comarca?'],
      objetivos: ['Reunirse con 3 agencias de viaje receptivas y entregarles material comercial y muestras de productos.', 'Firmar al menos 2 acuerdos marco de comercialización y derivación turística.', 'Establecer el protocolo de recepción de pasajeros del tren con las autoridades de La Trochita.'] ,
      check: ['Presentar el catálogo comercial en agencias receptivas de Esquel y Trevelin', 'Firmar acuerdos de comercialización turística con agencias locales', 'Acordar la coordinación operativa con los horarios de La Trochita']
    },
    { num: 7, f: '2026-10-30', t: 'ter', lugar: 'Casa de Piedra (Comunidad Nahuelpan)', hora: '10:30 a 13:30', titulo: 'Fam Tour vivencial en Casa de Piedra con informantes turísticos y test de la web', asistentes: ['adria', 'francisco', 'leandro'],
      guia: 'Simulacro vivencial completo con 12 invitados especiales (informantes de las Secretarías de Turismo de Esquel y Trevelin, guías del Parque y prestadores de la cohorte). Prueba en vivo del sistema de reservas web. Recepción y bienvenida protocolar a cargo de Nora Yanina Nahuelpan, taller de amasado participativo, caminata por La Huella del Cóndor hasta la vertiente, visita guiada al corral de Gallinas Mapuches y merienda comunitaria en Casa de Piedra con las tazas institucionales.',
      preguntas: ['¿El proceso de reserva digital y confirmación previa resultó fluido e intuitivo para los usuarios?', '¿Cómo respondieron los informantes turísticos a la conducción de Yanina en el taller de panificados?', '¿Qué devoluciones técnicas surgieron respecto a la señalización del sendero y los tiempos del recorrido?'],
      objetivos: ['Validar los tiempos operativos reales de la experiencia completa en condiciones simuladas.', 'Consolidar la seguridad y liderazgo de Yanina al frente de la conducción de contingentes.', 'Recoger encuestas de satisfacción y sugerencias de mejora de informantes y guías turísticos.'] ,
      check: ['Ejecutar el Fam Tour con 12 participantes del sector turístico comarcal', 'Completar encuestas de evaluación técnica sobre hospitalidad, guion y tiempos', 'Ajustar detalles finales de señalética y servicio de merienda antes del lanzamiento']
    },
    { num: 8, f: '2026-11-06', t: 'col', lugar: 'Centro Cultural Melipal / Espacio LAB', hora: '09:30 a 13:00', titulo: 'Ronda de negocios final, pitch de Yanina y Lauriano, y relanzamiento de Sabor Mapuche', asistentes: ['adria', 'francisco', 'leandro'],
      guia: 'Participación protagónica en el Encuentro de Cierre de Esquel LAB. Montaje de mesa temática con productos gourmet, tazas enlosadas, huevos celestes y frascos de sales de la abuela. Presentación en vivo de la plataforma web sabormapuche.com.ar. Pitch institucional de 3 minutos co-conducido por Nora Yanina Nahuelpan y Lauriano Ríos ante autoridades municipales, prestadores y prensa. Anuncio oficial de la 6ª edición del Festival Sabor Mapuche y firma de convenios comerciales.',
      preguntas: ['¿Qué impacto y visibilidad generó la presentación conjunta de Yanina y Lauriano en la comunidad turística?', '¿Cuáles son las reservas confirmadas en la plataforma web para el verano 2026/2027?', '¿Qué pasos inmediatos seguirán para la organización y sponsoreo de la 6ª edición del festival en noviembre?'],
      objetivos: ['Exhibir la oferta consolidada de turismo rural comunitario ante autoridades y medios de comunicación.', 'Consolidar convenios comerciales formales con agencias receptivas y comercios regionales.', 'Entregar el informe final de consultoría técnica y plan de acción para la temporada estival.'] ,
      check: ['Montar stand interactivo con degustación de productos, tazas y terminal web', 'Exponer el pitch de 3 minutos co-liderado por Yanina y Lauriano', 'Firmar acuerdos de comercialización formal y entrega de la hoja de ruta 2026/2027']
    }
  ],
  flypark: [
    { num: 1, f: '2026-09-17', t: 'ter', lugar: 'Predio de la Sociedad Rural de Esquel', hora: '14:00 a 16:30', titulo: 'Relevamiento de terreno en Sociedad Rural y planificación de montaje', asistentes: ['leandro', 'agustina'],
      guia: 'Visitar el predio de la Sociedad Rural junto a Fabricio y Paula Botto. Inspeccionar el declive natural del terreno con pasto, evaluar las dimensiones para el trazado de la pista modular de 100 m² (1 tira continua con desnivel) y verificar acometida eléctrica, baños y seguridad perimetral. Coordinar la solicitud formal de préstamo temporal por 3 semanas.',
      preguntas: ['¿Qué dimensiones exactas y pendiente requiere la bajada para asegurar un deslizamiento fluido sin riesgo de frenado brusco?', '¿Cuándo arriba el flete desde Buenos Aires con los 70 m² de alfombra Powplast adquiridos con el Plan Galina?', '¿Qué personal y herramientas necesitamos para montar la pista en una sola jornada sin impactar el suelo?'],
      objetivos: ['Delimitar la traza exacta de la pista de dryslope en el desnivel del predio rural.', 'Definir el cronograma de armado y recepción de materiales del Plan Galina.', 'Gestionar la nota formal de autorización de uso temporal ante la Comisión Directiva de la Rural.'],
      check: ['Medir la pendiente y distancia útil en el césped de la Sociedad Rural', 'Confirmar la llegada del cargamento de 70 m² de alfombra Powplast', 'Presentar nota oficial firmada por la Secretaría de Producción y el LAB']
    },
    { num: 2, f: '2026-09-24', t: 'ter', lugar: 'Predio de la Sociedad Rural de Esquel', hora: '10:00 a 14:00', titulo: 'Montaje de pista modular de dryslope, cajones y medidas de seguridad', asistentes: ['leandro', 'agustina'],
      guia: 'Jornada de ensamblado de los 100 m² de alfombra sintética Powplast entre el equipo de Flypark y colaboradores. Instalación modular por encastre tipo ladrillo sobre el pasto, fijación de anclajes no invasivos, colocación de rampita de inicio, cajón de deslizamiento y tubos de PVC para freestyle. Demarcación de la zona de frenado y colchón de seguridad.',
      preguntas: ['¿Cómo responde el encastre de las placas plásticas a las ondulaciones del terreno?', '¿Qué lubricación superficial con agua o silicona neutra requiere la alfombra para optimizar el deslizamiento?', '¿Qué delimitación visual con banderines y redes de contención colocamos en los laterales?'],
      objetivos: ['Dejar la pista de 100 m² completamente armada y operativa en el predio rural.', 'Testear el deslizamiento con tablas y esquís por parte de riders experimentados.', 'Verificar que la estructura no altere ni dañe el césped natural.'],
      check: ['Ensamblar la totalidad de las placas Powplast (100 m² cubiertos)', 'Montar 1 cajón y 1 riel de PVC para maniobras de freestyle', 'Realizar pruebas de deslizamiento y calibrar la zona de detención final']
    },
    { num: 3, f: '2026-10-01', t: 'ter', lugar: 'Sociedad Rural de Esquel', hora: '11:00 a 14:00', titulo: 'Evento Demo Oficial, registro para Chubut Deportes y respaldo político', asistentes: ['leandro', 'agustina'],
      guia: 'Presentación oficial y Demo de Flypark en La Rural antes del deadline del Plan Galina (7 de octubre). Convocatoria de autoridades provinciales y municipales (Intendente Taccetta, Chubut Deportes, Producción, Turismo), prensa comarcal, Club Andino y jóvenes riders. Demostración en vivo de freestyle e iniciación, fotografía y video oficial con la cartelería del Plan Galina para la rendición definitiva.',
      preguntas: ['¿Cómo comunicamos el logro histórico de tener esquí urbano todo el año en Esquel ante los medios?', '¿Qué tomas y comprobantes exige el formulario de rendición del Plan Galina para cerrar el expediente?', '¿Qué devoluciones y compromisos expresaron las autoridades presentes para la radicación definitiva?'],
      objetivos: ['Cerrar la rendición física y audiovisual del Plan Galina ante Chubut Deportes.', 'Generar un alto impacto mediático y comunitario positivo que blinde el proyecto ante trabas burocráticas.', 'Demostrar la factibilidad técnica y el atractivo del dryslope urbano en Esquel.'],
      check: ['Ejecutar la exhibición en vivo con riders y autoridades presentes', 'Registrar el set fotográfico con la cartelería oficial del Plan Galina', 'Presentar la carpeta de rendición completa ante Chubut Deportes']
    },
    { num: 4, f: '2026-10-08', t: 'ind', lugar: 'LAB Esquel / Secretaría de Turismo', hora: '10:30 a 12:30', titulo: 'Estructura legal mixta: Convenio Asociación Civil - Flypark y radicación', asistentes: ['leandro', 'agustina'],
      guia: 'Abordar la arquitectura legal del proyecto. Redactar el Convenio Marco de Articulación y Comodato entre la Asociación Civil Patagonia Freeride (titular de la pista financiada) y la firma comercial FLYPARK (operadora privada). Establecer con claridad la contraprestación social (cupos gratuitos para escuelas públicas y entrenamientos de clubes) y avanzar en las negociaciones del terreno definitivo de 1 hectárea.',
      preguntas: ['¿Cómo garantizamos la transparencia jurídica entre la asociación sin fines de lucro y la empresa comercial?', '¿Qué avances hubo con el propietario del terreno de Darwin y Humpreys para el comodato con opción a alquiler?', '¿Qué opciones de predios municipales o comodatos alternativos existen si se traba el terreno privado?'],
      objetivos: ['Redactar el borrador del convenio Asociación Civil - Flypark visado legalmente.', 'Definir el esquema de becas e iniciación escolar comunitaria (esquí social).', 'Elaborar la estrategia de negociación para el predio definitivo permanente.'],
      check: ['Borrador del convenio de comodato y articulación institucional cerrado', 'Fijar la cuota de horas semanales reservadas para clubes y escuelas públicas', 'Matriz comparativa de opciones de radicación territorial definitiva']
    },
    { num: 5, f: '2026-10-15', t: 'ind', lugar: 'LAB Esquel', hora: '10:30 a 12:30', titulo: 'Marco normativo municipal y protocolo de habilitación de dryslope', asistentes: ['leandro', 'agustina'],
      guia: 'Trabajar junto al equipo técnico del municipio en la formulación del anteproyecto de ordenanza marco para Parques y Pistas Urbanas de Deportes de Nieve Sintética (Dryslope). Redactar el protocolo operativo de bioseguridad: uso obligatorio de casco, protecciones corporales, seguro de accidentes personales / turismo activo y deslinde de responsabilidad.',
      preguntas: ['¿Bajo qué rubro municipal se encuadra hoy una actividad deportiva inédita en Chubut?', '¿Qué exigencias de salida de emergencia, matafuegos y botiquín de primeros auxilios se deben prever? ', '¿Qué aseguradoras operan pólizas de responsabilidad civil para deportes de acción en seco?'],
      objetivos: ['Redactar el anteproyecto de ordenanza municipal para elevar al Concejo Deliberante.', 'Consensuar los requisitos técnicos con el área de Habilitaciones Comerciales e Inspecciones.', 'Cotizar y seleccionar la póliza de seguro de turismo activo para usuarios.'],
      check: ['Presentar anteproyecto de ordenanza de dryslope en el Concejo Deliberante', 'Redactar el manual operativo de seguridad y deslinde de responsabilidad civil', 'Obtener cotizaciones de póliza de seguro de cobertura deportiva integral']
    },
    { num: 6, f: '2026-10-22', t: 'ind', lugar: 'LAB Esquel', hora: '10:30 a 12:30', titulo: 'Planilla paramétrica de costos, tarifario B2C/B2B y merchandising', asistentes: ['leandro', 'agustina'],
      guia: 'Estructurar la economía del emprendimiento. Definir costos fijos y variables (amortización de alfombra, alquiler, instructores, seguro, mantenimiento). Fijar el tarifario: pases por turno (45 y 90 min), abonos mensuales para residentes, pases familiares y tarifas para agencias de viajes (EVyT) y hoteles. Integrar la línea de indumentaria oficial de la marca FLYPARK (remeras, buzos, gorritos, buffs).',
      preguntas: ['¿Cuál es el precio de mercado de un pase de iniciación al esquí frente al costo de subir a La Hoya?', '¿Qué margen de comisión del 20-25% se ofrecerá a las agencias receptivas locales?', '¿Cuál es el margen y stock inicial de la línea de merchandising de autor FLYPARK?'],
      objetivos: ['Construir la planilla paramétrica de costos y punto de equilibrio operativo.', 'Emitir la lista de precios oficial para residentes, turistas y agencias receptivas.', 'Diseñar el packaging y fijar precios para la línea textil de FLYPARK.'],
      check: ['Cargar la planilla de costos unitarios y punto de equilibrio financiero', 'Emitir el tarifario comercial B2C y B2B para la temporada 2026/2027', 'Catálogo de precios de indumentaria propia (remeras, buzos, gorras y buffs)']
    },
    { num: 7, f: '2026-10-29', t: 'ind', lugar: 'LAB Esquel / Espacio Digital', hora: '10:30 a 12:30', titulo: 'Plataforma digital, canal de reservas y alianzas con agencias y clubes', asistentes: ['leandro', 'agustina'],
      guia: 'Diseño y lanzamiento de la landing page de FLYPARK en esquel.site con sistema de reservas por turnos y cobro anticipado por Mercado Pago. Firma de convenios marco de colaboración con clubes locales (Club Andino Esquel, Slalom Club, Esquel Ski Club) para pretemporada y entrenamientos continuos. Articulación de paquetes turísticos con agencias receptivas de Esquel.',
      preguntas: ['¿Qué horarios se reservan para entrenamientos federados y cuáles para turistas y principiantes?', '¿Cómo se gestiona el sistema de alquiler (rental) de tablas y botas en el predio?', '¿Qué convenios de co-branding podemos acordar con marcas de outdoor y sponsors?'],
      objetivos: ['Publicar la landing page oficial con video institucional y motor de reservas.', 'Firmar cartas de intención con al menos 2 clubes de montaña de Esquel.', 'Cerrar convenios de distribución comercial con al menos 2 agencias receptivas.'],
      check: ['Landing page operativa con botón de reserva y pasarela digital', 'Firmar actas de acuerdo con clubes deportivos locales', 'Distribuir material promocional en alojamientos y centros de información']
    },
    { num: 8, f: '2026-11-05', t: 'col', lugar: 'Centro Cultural Melipal / Espacio LAB', hora: '09:30 a 13:00', titulo: 'Ronda de Negocios Final, Lanzamiento Oficial y Hoja de Ruta Post-LAB', asistentes: ['leandro', 'agustina'],
      guia: 'Participación protagónica de FLYPARK en el Encuentro de Cierre de Esquel LAB como proyecto #1 de Acelera. Montaje de stand temático con placas de alfombra Powplast, esquís de freestyle, indumentaria oficial y proyección audiovisual de la demo en La Rural. Pitch de 3 minutos de Fabricio Guglielmetti. Anuncio de la radicación definitiva y entrega del plan operativo post-incubación.',
      preguntas: ['¿Qué balance arroja el proceso tras haber superado la urgencia del Plan Galina?', '¿Cuál es el cronograma de radicación definitiva para la temporada de verano 2026/2027?', '¿Qué articulación se proyecta con el Ministerio de Turismo y Deportes provincial?'],
      objetivos: ['Presentar oficialmente a FLYPARK como el primer parque urbano de esquí y snowboard de la provincia.', 'Consolidar compromisos comerciales con operadores turísticos y sponsors privados.', 'Entregar el informe de cierre de asistencia técnica y plan de expansión a 3 años.'],
      check: ['Montar stand interactivo de freestyle con equipamiento e indumentaria', 'Exponer el pitch de 3 minutos ante autoridades, prensa y sector privado', 'Firmar acuerdos comerciales y presentar la hoja de ruta definitiva']
    }
  ],
  lucero: [
    { num: 1, f: '2026-09-19', t: 'ter', lugar: 'Centro Integral Ecuestre Lucero (Ruta 259 km 25 / Los Cóndores)', hora: '10:00 a 12:30', titulo: 'Diagnóstico integral en predio, relevamiento de corrales, compost y El Pinar', asistentes: ['adria', 'noelia'],
      guia: 'Visitar el predio en Ruta 259 km 25. Relevar el circuito de corrales de la granja educativa (chinchillas, ratas de laboratorio, cerdita Rita Uva, Pamperito de 35 años), la pista de monta, el recambio de suelo con compost orgánico de septiembre y la matera histórica alemana. Evaluar accesibilidad y capacidad de carga.',
      preguntas: ['¿Qué capacidad de carga simultánea tienen los corrales para no estresar a los animales de rescate?', '¿Cómo organizamos los horarios turísticos (martes, miércoles y sábados) para no interferir con la docencia escolar de Sol ni con la equinoterapia de los viernes?', '¿Qué mejoras de señalética y senderos en El Pinar se requieren antes del inicio de temporada?'],
      objetivos: ['Mapear el circuito físico de la visita a la granja y el sendero ecuestre por El Pinar.', 'Relevar el ciclo de compostaje y mantenimiento de corrales para la experiencia pedagógica.', 'Definir el cronograma semanal de atención: martes, miércoles y sábados de 10:00 a 17:00.'],
      check: ['Inspeccionar corrales, pista de monta, boyero perimetral y matera histórica', 'Diseñar el plano de zonificación: granja perimetral, pista de prueba y bosque El Pinar', 'Fijar los días y horarios fijos semanales para visitas turísticas regulares']
    },
    { num: 2, f: '2026-09-26', t: 'ter', lugar: 'Centro Ecuestre Lucero', hora: '10:30 a 12:30', titulo: 'Estandarización de 2 Experiencias, Prueba en Pista y Seguros Patagonia Broker', asistentes: ['adria', 'noelia'],
      guia: 'Cerrar la ficha técnica y guion de las 2 experiencias turísticas: 1) Granja Educativa & Saberes Vivos (75 min, sin contacto con boyero, historias de rescate y mateada) y 2) Bautismo Ecuestre & Encuentro con el Caballo (90 min, vínculo pie a tierra, cepillado, prueba en pista para evaluar la mano del jinete y paseo en El Pinar). Protocolizar el alta previa obligatoria de seguros con Patagonia Broker / Mercantil Andina.',
      preguntas: ['¿Cuáles son los pasos etológicos en pista para evaluar al jinete y cuidar la boca del caballo antes de salir al bosque?', '¿Cómo diferenciamos los requerimientos de seguro entre la visita a la granja (interacción perimetral) y la actividad montada (póliza nominativa previa)?', '¿Qué elementos de protección (cascos homologados, polainas) se verificarán en cada turno?'],
      objetivos: ['Redactar el guion didáctico paso a paso de la visita a la granja y el bautismo ecuestre.', 'Formalizar el protocolo de prueba en pista obligatoria para resguardar el bienestar del animal.', 'Estructurar el formulario digital de recolección de datos personales y médicos para el seguro.'],
      check: ['Aprobar el guion interpretativo de las 2 experiencias turísticas', 'Redactar el protocolo de prueba en pista y deslinde de responsabilidad civil', 'Validar el circuito administrativo de pólizas con Patagonia Broker / Mercantil Andina']
    },
    { num: 3, f: '2026-10-03', t: 'ind', lugar: 'LAB Esquel / Secretaría de Turismo', hora: '10:30 a 12:30', titulo: 'Estructura de costos forrajeros, amortización invernal y tarifario dual B2C/B2B', asistentes: ['adria', 'noelia'],
      guia: 'Construir la planilla de costeo paramétrico. Desglosar forraje invernal (fardos de alfalfa y avena indispensables por falta de pasto en el pinar), veterinario, seguros y mantenimiento. Analizar el benchmark de mercado (Frisón Baroque $90.000 vs tarifas de Sol de $20.000/$40.000). Establecer un esquema tarifario dual: tarifa general para turistas y tarifa social para residentes de Esquel y Trevelin, más comisión del 20-25% para agencias EVyT.',
      preguntas: ['¿Cuánto forraje comercial se consume durante los meses de cierre invernal y cómo se amortiza con la temporada estival?', '¿Cuál es el precio justo de mercado para reposicionar a Lucero sin perder accesibilidad?', '¿Qué porcentaje de comisión se asigna a las agencias receptivas de Esquel y Trevelin?'],
      objetivos: ['Parametrizar el costo unitario de manutención de la manada y punto de equilibrio.', 'Emitir la lista oficial de precios: tarifa turista general, tarifa social residente y tarifa mayorista B2B.', 'Definir la política de reservas (50% de seña para congelar turno) y cancelaciones por mal tiempo.'],
      check: ['Cargar la planilla de costos forrajeros y amortización anual en Drive/Excel', 'Emitir la ficha tarifaria comercial dual (turista / residente / agencias)', 'Fijar el porcentaje de seña anticipada y protocolo de reprogramación climática']
    },
    { num: 4, f: '2026-10-10', t: 'ind', lugar: 'Centro Ecuestre Lucero', hora: '10:30 a 12:30', titulo: 'Desarrollo del Kit Pequeño Cuidador, Cuadernos de Historias y Merchandising', asistentes: ['adria', 'noelia'],
      guia: 'Prototipar el kit físico infantil Pequeño Cuidador de Animales / Guardián de Lucero (libreta de campo ilustrada, lápiz ecológico de madera, guía de huellas y cuidados, pin de guardián). Diseñar los cuadernos Historias de Lucero (el abuelo Toto Arrechea, Pamperito de 35 años, Rita Uva) y los paquetes de compost orgánico y semillas de la huerta como recuerdos sustentables.',
      preguntas: ['¿Qué relatos del abuelo Toto y de los animales rescatados generan mayor conexión emocional y pedagógica con los chicos?', '¿Qué costo unitario de imprenta permite ofrecer el kit a un precio accesible y rentable?', '¿Cómo se ambientará el rincón de recepción y venta artesanal junto a la matera histórica?'],
      objetivos: ['Cerrar el diseño gráfico y pedagógico del Kit del Pequeño Cuidador.', 'Cotizar una primera tirada de 100 cuadernillos y 100 pins con proveedores comarcales.', 'Diseñar el packaging sustentable de los sobres de compost de septiembre y semillas.'],
      check: ['Aprobar el boceto de la libreta de actividades del Pequeño Cuidador', 'Diseñar los stickers y empaques de compost orgánico y semillas de granja', 'Definir el precio de venta del kit y de los recuerdos en la matera histórica']
    },
    { num: 5, f: '2026-10-17', t: 'ter', lugar: 'Centro Ecuestre Lucero', hora: '10:00 a 13:00', titulo: 'Producción audiovisual 4K con dron y arquitectura de la plataforma web', asistentes: ['adria', 'noelia', 'leandro'],
      guia: 'Jornada de producción audiovisual profesional financiada por el LAB (cámaras 4K y dron en El Pinar) para rodar 3 cápsulas: 1) Raíces e Historia familiar (Sol Arrechea y el legado de Toto); 2) Bienestar y Conexión en El Pinar (tomas aéreas del bosque y monta consciente); y 3) Guardianes de Lucero (niños y animales de rescate). En paralelo, revisión técnica con Leandro Choi de la arquitectura de la plataforma web transaccional para reservas y seguros.',
      preguntas: ['¿Cuáles son las locaciones y ángulos aéreos más impactantes de El Pinar y los corrales para el vuelo del dron?', '¿Cómo estructurar la carga de datos del visitante en la web para automatizar la planilla de seguros antes de su llegada?', '¿Qué pasarela de pagos (Mercado Pago / transferencia) dejamos integrada para cobrar las señas?'],
      objetivos: ['Rodar el material audiovisual 4K y tomas con dron para las 3 cápsulas promocionales.', 'Validar la interfaz de reservas online: selección de días (mar/mié/sáb), turnos, cupos y formulario médico.', 'Generar un banco fotográfico de alta resolución para la web y redes sociales.'],
      check: ['Completar el plan de rodaje con cámaras 4K y tomas aéreas de dron', 'Aprobar el wireframe funcional de la plataforma web transaccional diseñada por Leandro', 'Seleccionar las tomas fotográficas para el catálogo digital y encabezados web']
    },
    { num: 6, f: '2026-10-24', t: 'ind', lugar: 'Oficinas de Turismo / Centro Cultural Melipal', hora: '10:30 a 12:30', titulo: 'Rueda comercial B2B con agencias de Esquel/Trevelin y sinergia con Haiku', asistentes: ['adria', 'noelia'],
      guia: 'Presentar formalmente la propuesta a las principales agencias receptivas de Esquel y Trevelin, destacando la ubicación estratégica sobre Ruta 259. Coordinar sinergias con prestadores de la cohorte: meriendas y retiros holísticos combinados en El Pinar junto a Haiku Casa de Té y True Patagonia. Planificar la fecha y articulación de la feria comunitaria El Paseo del Pinar.',
      preguntas: ['¿Qué interés manifiestan las agencias de Esquel y Trevelin en sumar una parada rural/ecuestre camino al Valle de los Tulipanes y Molino Nant Fach?', '¿Cómo coordinamos con Haiku la provisión de blends de té y vajilla para retiros en el bosque?', '¿Qué cronograma establecemos para la próxima edición de El Paseo del Pinar en primavera?'],
      objetivos: ['Presentar el catálogo oficial a 3 agencias receptivas habilitadas de la Comarca.', 'Firmar al menos 2 convenios de comercialización y derivación turística con agencias locales.', 'Acordar la realización de una experiencia piloto combinada Lucero + Haiku Casa de Té.'],
      check: ['Reunión comercial con agencias receptivas seleccionadas con catálogo en mano', 'Firmar acuerdos de comercialización turística con tarifario mayorista', 'Diseñar la propuesta del retiro holístico combinado en El Pinar con Haiku']
    },
    { num: 7, f: '2026-10-31', t: 'ter', lugar: 'Centro Ecuestre Lucero', hora: '10:30 a 13:00', titulo: 'Fam Tour en terreno con informantes turísticos y test de la plataforma web', asistentes: ['adria', 'noelia', 'leandro'],
      guia: 'Simulacro vivencial en campo con un grupo de prueba (informantes de Turismo de Esquel y Trevelin, guías y prestadores de la cohorte). Prueba en vivo del sistema de reservas web: registro previo de participantes y alta de seguros. Recorrido guiado completo por corrales, bautismo ecuestre con prueba de pista, entrega del Kit del Pequeño Cuidador y merienda campesina en la matera histórica.',
      preguntas: ['¿El proceso de reserva web y carga previa de datos para el seguro resultó ágil y claro para el usuario?', '¿Cómo valoraron los informantes turísticos la calidez del relato y el bienestar visible de los animales?', '¿Qué ajustes finales sugieren en cuanto a tiempos de recorrido y cartelería informativa?'],
      objetivos: ['Validar el funcionamiento operativo de la plataforma web y el alta de seguros en un caso real.', 'Cronometrar los tiempos de las experiencias para asegurar puntualidad entre turnos.', 'Recoger devoluciones técnicas de los informantes turísticos para su recomendación en mostrador.'],
      check: ['Ejecutar el Fam Tour con 10 informantes y prestadores invitados', 'Comprobar el circuito digital de reserva, confirmación y alta de póliza', 'Completar encuestas de satisfacción sobre seguridad, hospitalidad y contenido didáctico']
    },
    { num: 8, f: '2026-11-07', t: 'col', lugar: 'Centro Cultural Melipal / Espacio LAB', hora: '09:30 a 13:00', titulo: 'Ronda de negocios final, lanzamiento de plataforma web y pitch institucional', asistentes: ['adria', 'noelia', 'leandro'],
      guia: 'Participación protagónica en el Encuentro de Cierre de Esquel LAB. Montaje de mesa temática de Centro Ecuestre Lucero con exhibición de los kits de Pequeño Cuidador, frascos de compost orgánico, fotos de El Pinar y terminal interactiva con la plataforma web desarrollada por Leandro Choi. Pitch de 3 minutos de Sol Arrechea presentando Lucero: Bienestar, Arraigo Familiar y Conexión con la Manada. Firma de acuerdos comerciales y entrega de la hoja de ruta post-LAB.',
      preguntas: ['¿Cuáles son los resultados de reservas generadas durante los primeros días de la plataforma web?', '¿Cómo queda planificado el balance operativo de Sol para la temporada alta de verano 2026/2027?', '¿Qué pasos inmediatos se proyectan para la postulación a líneas de financiamiento municipal y provincial?'],
      objetivos: ['Presentar públicamente la plataforma web transaccional y las cápsulas audiovisuales 4K.', 'Consolidar acuerdos comerciales firmes con agencias receptivas y alojamientos de la Comarca.', 'Entregar el plan operativo de consultoría post-incubación y cronograma de temporada alta.'],
      check: ['Montar el stand interactivo con material de granja, kits infantiles y navegación web', 'Exponer el pitch de 3 minutos ante autoridades municipales, prestadores y medios', 'Firmar acuerdos de comercialización formal y entrega de la hoja de ruta 2026/2027']
    }
  ],
  arroyo: [
    { num: 1, f: '2026-09-18', t: 'ter', lugar: 'Márgenes del Arroyo Esquel (Punto cero: Av. Fontana y Arroyo)', hora: '15:00 a 17:30', titulo: 'Relevamiento en terreno del arroyo y delimitación de paradas sociohistóricas', asistentes: ['adria', 'francisco'],
      guia: 'Caminar las riberas del arroyo Esquel junto a Marcelo desde Av. Fontana hasta el Puente de Molinari. Identificar hitos de arqueología urbana (el Molino Weber, la vieja Planta de Gas, la fractura del terraplén ferroviario) y relevar accesibilidad de veredas, cruces peatonales y estado ambiental de las márgenes.',
      preguntas: ['¿Qué historias orales y personajes cotidianos podemos rescatar en cada uno de estos 5 puntos para que el visitante se sienta inmerso en la vida real de Esquel?', '¿Qué riesgos de transitabilidad o cruce identificamos para grupos de adultos mayores o familias con niños?', '¿Cómo abordamos didácticamente el contraste ambiental entre la limpieza de los años 90 y los desafíos actuales del cauce?'],
      objetivos: ['Mapear con GPS el trazado físico preliminar del Circuito 1 (El Origen entre el Arroyo y las Vías).', 'Definir las primeras 5 estaciones interpretativas y sus conceptos históricos vertebrales.', 'Establecer los criterios de seguridad peatonal y tiempos de permanencia en cada parada.'],
      check: ['Caminar el tramo Fontana-Molinari y registrar coordenadas de 5 estaciones clave', 'Documentar el estado de veredas, pasarelas y accesos a las márgenes del arroyo', 'Fijar la duración máxima de la caminata peatonal en 90 minutos netos']
    },
    { num: 2, f: '2026-09-25', t: 'ter', lugar: 'Barrio Ceferino / Gruta del Padre Parolini', hora: '15:00 a 17:30', titulo: 'Prospección en laderas, mirador de La Gruta y articulación vecinal', asistentes: ['adria', 'francisco'],
      guia: 'Recorrer el ascenso peatonal hacia La Gruta del Ceferino por la escalinata barrial. Evaluar el mirador panorámico como estación de interpretación urbana (donde se observa el crecimiento de la ciudad). Contactar a vecinas del barrio (Doña Pepa / elaboradoras de tortas fritas) para evaluar la factibilidad de una parada de mate y sabor campesino.',
      preguntas: ['¿Cómo reciben los vecinos del barrio la idea de que un grupo reducido de turistas suba a la gruta y comparta una merienda tradicional?', '¿Qué relatos del Padre Parolini y de los primeros pobladores de las laderas le dan mística al mirador?', '¿Cuál es el esfuerzo físico real que demanda subir la escalinata y cómo lo adaptamos para distintos públicos?'],
      objetivos: ['Definir la viabilidad del Circuito 2 (Laderas, Gruta y Memoria Viva).', 'Acordar con una vecina o comercio barrial la provisión de tortas fritas caseras y servicio de mate.', 'Diseñar la parada en La Gruta como punto cúspide de la experiencia inmersiva.'],
      check: ['Cronometrar los tiempos de ascenso y descenso por la escalinata del Ceferino', 'Entrevistar a vecinas del sector para consensuar la dinámica de bienvenida comunitaria', 'Asegurar condiciones de higiene y calidez para la merienda popular en el mirador']
    },
    { num: 3, f: '2026-10-02', t: 'ind', lugar: 'LAB Esquel / Secretaría de Turismo', hora: '10:30 a 12:30', titulo: 'Estructuración del guion interpretativo y diseño del libro-souvenir', asistentes: ['adria', 'francisco'],
      guia: 'Trabajar en gabinete la dramaturgia del guiado: transformar la erudición histórica en un relato empático y emocionante de 90 a 120 minutos. Seleccionar las anécdotas, fotografías de época y documentos que componen el libro-souvenir Postales históricas de Esquel, definiendo su formato de entrega y packaging artesanal.',
      preguntas: ['¿Cómo dosificamos la información para que el visitante no se sienta abrumado por fechas, sino conmovido por la vivencia humana?', '¿Qué formato físico de folleto de bolsillo o postal coleccionable acompaña al libro para entregar durante la caminata?', '¿Cómo articulamos la figura de Marcelo como anfitrión inspirador con futuros guías jóvenes o estudiantes de turismo co-anfitriones?'],
      objetivos: ['Cerrar el guion narrativo modular de los dos circuitos (estaciones, relatos, remates y preguntas reflexivas).', 'Prototipar la presentación editorial del libro Postales históricas de Esquel como souvenir de alto valor.', 'Elaborar el decálogo de buenas prácticas de turismo comunitario y respeto vecinal.'],
      check: ['Aprobar el guion literario y técnico de las 5 estaciones del Circuito 1', 'Definir el costo de imprenta y precio del libro-souvenir de autor', 'Diseñar el tríptico de mano con el mapa de la cuenca y puntos de memoria']
    },
    { num: 4, f: '2026-10-09', t: 'ind', lugar: 'LAB Esquel', hora: '10:30 a 12:30', titulo: 'Estructura de costos, tarifario y alianzas con comercios del arroyo', asistentes: ['adria', 'francisco'],
      guia: 'Definir el modelo económico de la experiencia. Desglosar costos: honorarios del historiador/guía, compra de tortas fritas/refrigerio a vecinas, costo de impresión de postales/souvenirs y margen de comercialización. Fijar precio por persona (B2C), tarifa diferenciada para residentes y comisión del 20-25% para agencias receptivas y hoteles.',
      preguntas: ['¿Cuál es el tamaño óptimo de grupo (mínimo 4, máximo 12 personas) para no perder la intimidad del relato?', '¿Qué acuerdo comercial establecemos con Panadería Genes o el Molino para paradas de café y descanso?', '¿Qué días y horarios fijos semanales se ofrecerá la salida regular para facilitar la venta en agencias?'],
      objetivos: ['Construir la planilla paramétrica de costos y punto de equilibrio por salida.', 'Fijar el tarifario comercial oficial B2C y B2B para la temporada primavera/verano 2026.', 'Establecer el calendario de salidas regulares (ej. viernes y sábados por la tarde).'],
      check: ['Calcular costo por pasajero y punto de equilibrio financiero (4 pasajeros)', 'Emitir la ficha tarifaria formal para agencias receptivas de Esquel', 'Formalizar el acuerdo de consumo y parada técnica con Panadería Genes / comercio local']
    },
    { num: 5, f: '2026-10-16', t: 'ind', lugar: 'LAB Esquel / Espacio Digital', hora: '10:00 a 12:30', titulo: 'Plataforma digital, autoguiado interactivo y pasarela de cobro', asistentes: ['adria', 'francisco', 'leandro'],
      guia: 'Diseñar la landing page oficial de El Arroyo que nos ve crecer.... Cargar el mapa geolocalizado en la web de turismo de Esquel con el circuito autoguiado (audioguías breves y fotos históricas de los años 30-50). Configurar el canal de reservas por WhatsApp Business y la pasarela de pagos con Mercado Pago para cobro online de las caminatas guiadas.',
      preguntas: ['¿Qué locuciones breves (1 minuto por estación) puede grabar Marcelo para el audioguía digital?', '¿Cómo facilitamos que un vecino o turista escanee el código QR en la vía pública sin necesidad de app pesada?', '¿Qué datos de contacto y comprobantes de reserva automatizamos para el turista?'],
      objetivos: ['Maquetar la landing page con la identidad visual del proyecto y mapa interactivo.', 'Grabar 5 micro-audios testimoniales de Marcelo para el sistema autoguiado.', 'Configurar la cuenta de cobro digital Mercado Pago vinculada al sistema de reservas.'],
      check: ['Publicar la landing page informativa y el mapa interactivo del recorrido', 'Subir los 5 archivos de audio interpretativo al servidor de Esquel Destino', 'Validar el link de pago y el formulario de reserva online']
    },
    { num: 6, f: '2026-10-23', t: 'ind', lugar: 'Oficinas de Turismo / Centro Cultural Melipal', hora: '10:30 a 12:30', titulo: 'Sinergias de cohorte (Corcho Bikes, La Trochita) y rueda con agencias', asistentes: ['adria', 'francisco'],
      guia: 'Articulación intersectorial: reunirse con Néstor Colinecul (Corcho Bikes) para diseñar la variante Cicloturismo Histórico del Arroyo (alquiler de bicis + guiado). Presentar la caminata a agencias receptivas locales (EVyT) y prestadores hoteleros como complemento ideal para días sin excursiones largas o tardes libres de La Trochita.',
      preguntas: ['¿Cómo sincronizamos el horario de llegada del tren de La Trochita con el inicio de la caminata en Av. Fontana?', '¿Qué tarifa combinada bici-tour podemos estructurar junto a Corcho Bikes?', '¿Qué material gráfico impreso dejamos en los mostradores de información turística y recepciones de hoteles?'],
      objetivos: ['Definir el paquete combinado de cicloturismo histórico junto a Corcho Bikes.', 'Firmar al menos 2 acuerdos de comercialización y derivación con agencias receptivas habilitadas.', 'Distribuir el folleto promocional en los centros de informes turísticos municipales.'],
      check: ['Redactar la ficha de producto combinado bici-tour con Corcho Bikes', 'Reunirse con 3 agencias de turismo receptivo de Esquel', 'Dejar material promocional impreso en la Secretaría de Turismo y Melipal']
    },
    { num: 7, f: '2026-10-30', t: 'ter', lugar: 'Márgenes del Arroyo Esquel y Mirador del Ceferino', hora: '15:30 a 18:00', titulo: 'Caminata de validación en campo (Fam Tour con informantes y guías)', asistentes: ['adria', 'francisco', 'leandro'],
      guia: 'Salida piloto en terreno con un grupo de prueba de 8 personas (informantes turísticos municipales, guías de turismo matriculados y vecinos invitados). Ejecución completa del guiado, paradas interpretativas, merienda de tortas fritas con doña Pepa y entrega simbólica del libro-souvenir. Medición de tiempos, retroalimentación y ajuste de dinámicas grupales.',
      preguntas: ['¿Cómo funcionó el ritmo de marcha y la acústica urbana del relato en cada parada?', '¿Qué emociones y aprendizajes destacaron los participantes en la encuesta de satisfacción?', '¿Qué ajustes finales requiere el trato con los vecinos y la logística de la merienda?'],
      objetivos: ['Validar la experiencia integral en condiciones reales de operación turística.', 'Recopilar encuestas de calidad, testimonios y sugerencias de mejora del sector profesional.', 'Generar registro fotográfico y en video de la caminata para promoción en redes y medios.'],
      check: ['Realizar el Fam Tour completo con 8 participantes invitados', 'Completar planilla de evaluación de tiempos, narrativa y paradas gastronómicas', 'Obtener banco de fotos y clips audiovisuales de la experiencia en campo']
    },
    { num: 8, f: '2026-11-06', t: 'col', lugar: 'Centro Cultural Melipal / Espacio LAB', hora: '09:30 a 13:00', titulo: 'Ronda de negocios final, lanzamiento oficial y hoja de ruta post-LAB', asistentes: ['adria', 'francisco', 'leandro'],
      guia: 'Participación protagónica en el Encuentro de Cierre de Esquel LAB. Montaje del espacio de El Arroyo que nos ve crecer... con exhibición del libro Postales históricas de Esquel, mapas de cuenca y fotografías antiguas. Pitch de 3 minutos de Marcelo Troiano presentando el producto ante operadores turísticos, prensa y autoridades. Entrega del plan de continuidad comercial y sustentabilidad comunitaria.',
      preguntas: ['¿Qué balance arroja el proceso de aceleración y cómo se siente Marcelo en su nuevo rol de anfitrión turístico?', '¿Cómo se proyecta la incorporación de jóvenes guías del CFP Nº 655 para la temporada alta de verano?', '¿Qué articulación se mantendrá con la Secretaría de Turismo para eventos culturales y fechas históricas?'],
      objetivos: ['Presentar oficialmente el producto turístico ante el ecosistema turístico y los medios de prensa.', 'Consolidar compromisos comerciales firmes de comercialización para el verano 2026/2027.', 'Entregar el informe de cierre de asistencia técnica y manual operativo del recorrido.'],
      check: ['Montar mesa expositora con libros, fotos patrimoniales y postales históricas', 'Exponer el pitch de 3 minutos ante prestadores y autoridades municipales', 'Firmar cartas de compromiso comercial con agencias y hoteles de Esquel']
    }
  ],
  carpint: [
    { num: 1, f: '2026-09-17', t: 'ter', lugar: 'Taller Carpintero Esquel (Brown 1570, B° Estación)', hora: '10:00 a 12:30', titulo: 'Diagnóstico en Taller de Barrio Estación y Relevamiento del Torno', asistentes: ['mariela', 'cesia'],
      guia: 'Visitar el taller hogareño de Maxi en calle Brown 1570. Relevar el torno de madera, las gubias, herramientas de lijado, acopio de troncos y tablones nobles (lenga, radal, ciprés). Evaluar el lay-out actual para definir la zona segura para visitantes (a 1,5 m de la bancada del torno).',
      preguntas: ['¿Cuánto tiempo real te insume tornear un cuenco mediano desde el trozo en bruto hasta el lijado final?', '¿Qué trabajos pesados te obligan sí o sí a trasladar madera al CAPEC y cuánto pagás por el canon CAM?', '¿Cómo resolvés el frío y la lluvia en el patio durante el invierno para no frenar la producción?'],
      objetivos: ['Relevar medidas y lay-out del taller para delimitar la zona de visita y zona segura del torno.', 'Identificar las piezas con mayor demanda histórica y mayor margen de ganancia artesanal.', 'Establecer el cronograma de trabajo conjunto y disponibilidad matutina de Maxi.'],
      check: ['Inspeccionar torno de madera, herramientas y stock de maderas nobles en taller', 'Medir distancias de seguridad y demarcar visualmente la zona de visitantes a 1,5 m', 'Listar los 4 productos candidatos a conformar la Línea Turística Estrella']
    },
    { num: 2, f: '2026-09-24', t: 'ind', lugar: 'Taller Carpintero Esquel', hora: '10:30 a 12:30', titulo: 'Estandarización de las 4 Piezas Estrella y Acabados Alimentarios', asistentes: ['mariela', 'cesia'],
      guia: 'Seleccionar y cerrar las 4 piezas definitivas de la línea turística (Cuenco Mediano, Mate Torneado con Sello, Plato de Asado y Cuchara Rústica). Estandarizar dimensiones, tiempos de torno, tipo de madera y protocolo de curado alimentario seguro con cera de abejas y aceite mineral.',
      preguntas: ['¿Qué tratamiento o sellador usás actualmente para que los cuencos y mates no se rajen ni transmitan sabores extraños?', '¿Cuál es el costo unitario de lija, sellador y marca a fuego por pieza?', '¿Podemos garantizar un stock mínimo de 10 unidades por pieza para abastecer consignaciones iniciales?'],
      objetivos: ['Cerrar la ficha técnica con dimensiones y tolerancias de cada una de las 4 piezas estrella.', 'Validar la fórmula de curado artesanal inocuo y apto para alimentos calientes y fríos.', 'Diseñar la etiqueta colgante de madera o papel kraft que certifique autenticidad y origen cordillerano.'],
      check: ['Definir medidas exactas de cuenco, mate, plato y cuchara rústica', 'Aprobar el protocolo de curado inocuo con cera de abejas virgen', 'Diseñar el boceto de etiqueta colgante con historia de Barrio Estación y cuidados de la madera']
    },
    { num: 3, f: '2026-10-01', t: 'ind', lugar: 'Oficinas de Turismo / LAB Esquel', hora: '10:30 a 12:30', titulo: 'Costeo Integral, Tiempos de Maquinado y Tarifario Mayorista/Minorista', asistentes: ['mariela', 'cesia'],
      guia: 'Construir la planilla de costeo paramétrico de las 4 piezas. Desglosar costo de pie de madera, canon de uso del CAPEC, desgaste de herramientas/lijas, cera, marca a fuego, y cuantificar la mano de obra calificada por hora de torno. Fijar precios sugeridos al público y precio mayorista para comercios (margen del 30-40% para el revendedor).',
      preguntas: ['¿Cuántas piezas podés producir por semana sin descuidar pedidos de carpintería tradicional?', '¿A qué precio vendías antes tus piezas y qué margen real te quedaba tras comprar insumos?', '¿Qué porcentaje de comisión o descuento comercial resulta viable ofrecer a regionales y hoteles?'],
      objetivos: ['Parametrizar el costo unitario real de cada pieza en planilla Excel/Drive.', 'Establecer el precio de venta mayorista y el precio de venta sugerido al público (PVP).', 'Fijar la política de reposición y plazos de pago para tiendas en consignación.'],
      check: ['Cargar la planilla de costos unitarios con amortización de herramientas y CAPEC', 'Emitir la lista de precios oficial mayorista y minorista de la Línea Turística', 'Redactar modelo de remito y acta de entrega en consignación a 15 días']
    },
    { num: 4, f: '2026-10-08', t: 'ter', lugar: 'Taller Carpintero Esquel', hora: '10:00 a 12:30', titulo: 'Guion Vivencial Taller Abierto en Barrio Estación y Protocolo de Seguridad', asistentes: ['mariela', 'cesia'],
      guia: 'Estructurar la experiencia vivencial turística para grupos reducidos (2 a 4 personas). Diseñar la secuencia: bienvenida con reseña del oficio y Barrio Estación, demostración de torneado en vivo, participación guiada del turista en lijado de su propia pieza, marcado a fuego y cierre con mateada campesina. Validar el protocolo de seguridad y zona restringida.',
      preguntas: ['¿Qué sensaciones y anécdotas del oficio de carpintero querés transmitirle al visitante durante la demostración?', '¿En qué momento exacto de la pieza participa el turista para que sea una experiencia segura y sin riesgo de corte?', '¿Cómo articulamos con tu familia la preparación del mate y las tortas fritas para la bienvenida?'],
      objetivos: ['Redactar el guion vivencial paso a paso de 60 a 75 minutos de duración.', 'Formalizar el protocolo de bioseguridad y elementos de protección (antiparras obligatorias).', 'Definir el valor de la entrada o ticket por persona que incluya la pieza personalizada de recuerdo.'],
      check: ['Redactar el guion de la experiencia Taller Abierto en Barrio Estación', 'Comprobar stock de antiparras de seguridad y cartelería de precaución en torno', 'Simular la secuencia de lijado participativo y marcado a fuego con el equipo técnico']
    },
    { num: 5, f: '2026-10-15', t: 'ter', lugar: 'Taller Carpintero Esquel', hora: '10:00 a 13:00', titulo: 'Producción Fotográfica 4K, Catálogo Digital y Piezas de Comunicación', asistentes: ['mariela', 'cesia', 'leandro'],
      guia: 'Sesión fotográfica y de video profesional 4K en el taller con iluminación natural y encuadres de virutas volando en el torno. Registrar las 4 piezas terminadas y la dinámica vivencial con el artesano. Armar el catálogo digital interactivo para envío a agencias receptivas y hotelería de alta gama.',
      preguntas: ['¿Qué piezas y maderas tienen mejor veta y presencia visual para el plano detalle de fotografía?', '¿Cómo querés presentar tu historia personal en el texto de portada del catálogo digital?', '¿Qué canales de contacto directo (WhatsApp comercial, Instagram) vamos a vincular al catálogo?'],
      objetivos: ['Generar un banco de 25 fotografías profesionales en alta resolución y clips cortos de torno en vivo.', 'Maquetar el catálogo digital PDF de la Línea Turística con ficha técnica de cada madera noble.', 'Configurar el perfil de Instagram y WhatsApp Business con catálogo integrado de productos.'],
      check: ['Realizar sesión fotográfica de piezas individuales y demostración en torno', 'Seleccionar las 15 mejores imágenes y redactar reseñas de cada producto', 'Generar el catálogo digital interactivo en formato PDF y enlace web']
    },
    { num: 6, f: '2026-10-22', t: 'ind', lugar: 'CAPEC Esquel / Secretaría de Producción', hora: '10:30 a 12:30', titulo: 'Articulación CAPEC, Proyecto de Infraestructura y Rueda Comercial B2B', asistentes: ['mariela', 'cesia', 'leandro'],
      guia: 'Reunión de trabajo con la coordinación del CAPEC (Paula Botto) y Producción. Presentar la formulación de proyecto para subsidio o microcrédito destinado al cerramiento térmico y techado definitivo del taller. Planificar la ronda de visitas a los primeros comercios céntricos y hoteles boutique para colocación en consignación.',
      preguntas: ['¿Cuáles son los montos máximos y requisitos de la línea de financiamiento productivo vigente en CAPEC?', '¿Qué comercios de regionales y chocolaterías del centro tienen el perfil de cliente adecuado para estas piezas nobles?', '¿Qué volumen de stock de piezas estrella tenemos listo para dejar en consignación esta semana?'],
      objetivos: ['Presentar el presupuesto de materiales para el cerramiento del taller ante la Secretaría de Producción.', 'Visitar 3 comercios regionales de Esquel y presentar catálogo y muestras físicas de producto.', 'Firmar acuerdos de consignación con remito oficial a 15 días con al menos 2 comercios.'],
      check: ['Entregar anteproyecto técnico y presupuesto de cerramiento del taller en CAPEC', 'Visitar comercios céntricos seleccionados con muestras físicas y catálogo en mano', 'Dejar stock inicial de 5 cuencos, 5 mates y 5 tablas en al menos 2 comercios adheridos']
    },
    { num: 7, f: '2026-10-29', t: 'ter', lugar: 'Taller Carpintero Esquel (Brown 1570)', hora: '10:30 a 12:30', titulo: 'Ensayo General de Taller Abierto (Fam Press y Validación en Campo)', asistentes: ['mariela', 'cesia'],
      guia: 'Simulacro completo de la experiencia vivencial con un grupo de prueba (informantes de la Secretaría de Turismo y guías invitados). Evaluar el manejo de tiempos, la claridad de la demostración en torno, la seguridad del lijado participativo, el impacto del mate con tortas fritas caseras y la venta espontánea de piezas adicionales.',
      preguntas: ['¿Cómo se sintió Maxi al hablar y explicar frente a personas desconocidas mientras operaba el torno?', '¿El espacio de estacionamiento en calle Brown y el acceso peatonal al patio resultaron cómodos para los visitantes?', '¿Qué dudas o sugerencias plantearon los participantes durante la mateada final?'],
      objetivos: ['Validar la duración real de la experiencia (meta: 60 minutos cronometrados sin desvíos).', 'Testear la comprensión de normas de seguridad y la satisfacción del visitante al estampar su pieza a fuego.', 'Ajustar detalles del guion verbal y del rincón de exhibición de piezas a la venta.'],
      check: ['Ejecutar el simulacro de la experiencia vivencial con 4 invitados externos', 'Recoger encuestas de feedback sobre seguridad, calidez y valor percibido del souvenir', 'Monitorear ventas de las primeras consignaciones en comercios céntricos y cobrar remitos cumplidos']
    },
    { num: 8, f: '2026-11-05', t: 'col', lugar: 'Centro Cultural Melipal / Espacio LAB', hora: '09:30 a 13:00', titulo: 'Ronda de Negocios Final, Lanzamiento Oficial y Hoja de Ruta Post-LAB', asistentes: ['mariela', 'cesia', 'leandro'],
      guia: 'Participación protagónica en el Encuentro de Cierre y Ronda de Negocios de Esquel LAB. Montaje de mesa de exhibición de torneado y piezas de maderas nobles. Pitch de 3 minutos de Maxi presentando Carpintero Esquel: Artefactos con Alma de Bosque y Taller Abierto en Barrio Estación. Firma de acuerdos comerciales con prestadores turísticos y entrega del plan operativo post-incubación.',
      preguntas: ['¿Qué balance arrojan las primeras ventas en consignación y qué producto tuvo mayor rotación?', '¿Cómo organizará Maxi su calendario semanal entre producción de taller, CAPEC y visitas turísticas agendadas?', '¿Qué apoyo adicional requerirá para la ejecución de la obra del taller cuando se apruebe el financiamiento?'],
      objetivos: ['Exhibir la Línea Turística Estrella completa ante operadores turísticos, guías y autoridades municipales.', 'Consolidar al menos 3 acuerdos de comercialización continua con hoteles y tiendas de diseño local.', 'Entregar el informe de cierre de asistencia técnica y hoja de ruta productiva para la temporada 2026/2027.'],
      check: ['Armar stand con virutas de madera, torno fotográfico y las 4 piezas estrella terminadas', 'Exponer el pitch de 3 minutos ante el ecosistema turístico y comercial de Esquel', 'Firmar actas de compromiso comercial y balance de cobro de las primeras ventas']
    }
  ],
  senderos: [
    { num: 1, f: '2026-09-18', t: 'ter', lugar: 'Acceso La Cascada / Senda Cámpora', hora: '09:30 a 12:30', titulo: 'Relevamiento en terreno del Sendero La Cascada y punto cero', asistentes: ['adria', 'francisco'],
      guia: 'Caminar el circuito accesible de La Cascada. Identificar especies nativas presentes (llantén, paramela, palo piche, rosa mosqueta), medir tiempos de caminata pausada y seleccionar los 3 puntos de parada interpretativa.',
      preguntas: ['¿Cuánto tiempo real demanda recorrer el sendero parando a observar 5 plantas?', '¿Qué puntos ofrecen sombra y bancos para la merienda final?'],
      objetivos: ['Mapear las 5 paradas botánicas del sendero La Cascada.', 'Cronometrar la duración total (máximo 1h 45m).', 'Fotografiar las plantas nativas para el tríptico.'],
      check: ['Mapear las 5 paradas botánicas en La Cascada', 'Cronometrar duración de caminata pausada', 'Fotografiar especies nativas para el tríptico']
    },
    { num: 2, f: '2026-09-22', t: 'ter', lugar: 'Cañadón de Bórquez a La Zeta', hora: '15:30 a 18:30', titulo: 'Traqueo del Cañadón de Bórquez a La Zeta y diseño del tríptico', asistentes: ['francisco'],
      guia: 'Realizar el traqueo GPS del sendero del Cañadón de Bórquez hasta Laguna La Zeta. Revisar el borrador del tríptico de bolsillo y definir el contenido botánico y didáctico.',
      preguntas: ['¿Cómo resolvemos los tramos con pendiente para personas no habituadas a la montaña?', '¿Qué información técnica debe incluir el folleto sin saturar al turista?'],
      objetivos: ['Track GPS exportado en formato GPX/KML.', 'Textos y fichas botánicas del tríptico corregidos.', 'Selección de especies para el desafío fotográfico.'],
      check: ['Traquear recorrido con GPS hasta La Zeta', 'Revisar textos botánicos del tríptico de bolsillo', 'Definir dinámica de desafío fotográfico']
    },
    { num: 3, f: '2026-09-25', t: 'ind', lugar: 'Protección Civil Municipal', hora: '09:30 a 12:00', titulo: 'Protocolo de seguridad, evacuación y taller de comunicaciones VHF', asistentes: ['adria'],
      guia: 'Diseñar el Plan de Contingencias y Evacuación de los 3 senderos. Establecer frecuencias de radio VHF/UHF, pautas de aviso a Defensa Civil y repaso de botiquín WFR.',
      preguntas: ['¿Qué protocolo seguimos si un turista sufre un esguince en el Cañadón de las Palomas?', '¿Cuáles son los puntos de extracción vehicular más cercanos?'],
      objetivos: ['Ficha de Gestión del Riesgo y Plan de Evacuación firmada.', 'Pauta de frecuencias de radio municipal asignada.', 'Protocolo de aptitud física previa para excursionistas.'],
      check: ['Diseñar plan de evacuación para los 3 senderos', 'Fijar frecuencias VHF con Protección Civil', 'Verificar botiquín agreste WFR/RCP']
    },
    { num: 4, f: '2026-09-30', t: 'gru', lugar: 'Célula 3 (Melipal)', hora: '09:30 a 12:30', titulo: 'Costeo de salidas y matriz de precios (Célula 3)', asistentes: ['adria', 'francisco'],
      guia: 'Calcular la estructura de costos de cada salida: honorarios de las guías, seguro de accidentes personales, impresión de trípticos, insumos del souvenir cosmético, merienda campestre y comisión para agencias (20%).',
      preguntas: ['¿Cuánto vale el servicio guiado de 2 horas con souvenir incluido?', '¿Cuál es el cupo mínimo para no salir a pérdida?'],
      objetivos: ['Planilla de costos fijos y variables completada.', 'Punto de equilibrio fijado en 4 pasajeros.', 'Tarifario para venta directa y tarifa neta para agencias hoteleras.'],
      check: ['Completar matriz de costeo por salida', 'Fijar punto de equilibrio en 4 pasajeros', 'Establecer tarifario B2C y tarifa neta B2B']
    },
    { num: 5, f: '2026-10-06', t: 'ter', lugar: 'Mirador La Zeta', hora: '15:30 a 18:00', titulo: 'Ensayo del guion sensorial y dinámica de "bajada a tierra"', asistentes: ['francisco'],
      guia: 'Probar en terreno la dinámica de meditación, descalzado y respiración consciente en el mirador. Ajustar la transición hacia la mateada campesina y la cata de aromas.',
      preguntas: ['¿Cómo rompemos la timidez del turista urbano para que se anime a sacarse las zapatillas?', '¿Qué tono de voz y silencios acompañan mejor la experiencia?'],
      objetivos: ['Guion de facilitación sensorial cronometrado (15 minutos de meditación).', 'Logística de termo, mates individuales y hierbas probada en mochila.', 'Protocolo de higiene para degustación.'],
      check: ['Cronometrar pausa de respiración y descalzado', 'Probar cebado de mates con paramela y llantén', 'Estandarizar guion narrativo de cierre']
    },
    { num: 6, f: '2026-10-19', t: 'ind', lugar: 'CAPEC / Producción', hora: '10:00 a 12:30', titulo: 'Vinculación institucional con CAPEC y registro de prestadores', asistentes: ['adria'],
      guia: 'Evaluar el marco normativo para la elaboración de cosmética natural en salas comunitarias bajo estándares ANMAT/provinciales. Iniciar trámite en el Registro de Turismo Alternativo de la Subsecretaría de Turismo.',
      preguntas: ['¿Qué requisitos de rotulado y loteado exige la comercialización turística de cosméticos?', '¿Cómo formalizamos el alta en turismo alternativo municipal?'],
      objetivos: ['Diagnóstico de adecuación de envases y etiquetas con CAPEC.', 'Formulario de inscripción en Turismo Alternativo completado.', 'Asesoramiento contable para monotributo.'],
      check: ['Reunión técnica en CAPEC con Paula Botto', 'Completar solicitud de Turismo Alternativo', 'Definir encuadre de facturación y monotributo']
    },
    { num: 7, f: '2026-10-23', t: 'ter', lugar: 'Circuito Cañadón de Bórquez', hora: '09:30 a 13:00', titulo: 'Salida piloto con público testigo y producción de contenidos', asistentes: ['adria', 'francisco'],
      guia: 'Ejecutar la experiencia completa con un grupo testigo real (invitados de taekwondo y equipo municipal). Registrar fotografías profesionales y clips de video para redes sociales y folletería digital.',
      preguntas: ['¿Cómo reaccionan los participantes ante el desafío del tríptico y el momento de descalzarse?', '¿Qué ajustes de ritmo se requieren?'],
      objetivos: ['Salida piloto completada con 10 personas.', 'Encuestas de satisfacción recogidas.', 'Banco de 25 fotos profesionales y 3 reels en alta definición.'],
      check: ['Guiar salida piloto con 10 asistentes', 'Levantar encuestas de satisfacción', 'Obtener banco de imágenes y clips de video']
    },
    { num: 8, f: '2026-11-10', t: 'cie', lugar: 'Melipal', hora: '10:00 a 14:00', titulo: 'Rueda de negocios en Melipal y lanzamiento de salidas', asistentes: ['leandro', 'adria', 'francisco', 'mariela', 'agustina', 'cesia', 'noelia'],
      guia: 'Presentar el producto terminado ante agencias receptivas, hoteles y comercios comarcales en la rueda de negocios. Exhibir el stand interactivo con ramas secas, muestras de ungüentos y trípticos.',
      preguntas: ['¿Qué agencias de Esquel incorporan las salidas regulares a su tarifario de verano?', '¿Cómo queda enlazado el botón de reserva en la web oficial?'],
      objetivos: ['Stand montado con experiencia de aromas y cosmética.', 'Pitch de 3 minutos de Gabriela y Florencia.', 'Firma de cartas de intención con al menos 2 agencias receptivas locales.'],
      check: ['Montar stand interactivo con cosmética y flora seca', 'Exponer pitch de 3 minutos ante prestadores', 'Firmar al menos 2 cartas de intención con agencias']
    }
  ]
};

function generateMeetingsFor(p, idx) {
  if (exactMeetings[p.id]) return exactMeetings[p.id];
  
  const m = [];
  const offset = (idx % 3);
  const sr = p.sr;
  const jr = p.jr;
  const asist = [sr, jr];
  
  m.push({
    num: 1, f: `2026-09-${11 + offset}`, t: (p.linea === 'Raíz' ? 'ter' : 'ind'),
    lugar: (p.linea === 'Raíz' ? 'Predio / Chacra' : 'Taller / Local'),
    hora: '10:00 a 11:30',
    titulo: 'Diagnóstico en terreno y relevamiento de recursos',
    asistentes: asist,
    guia: `Conocer el espacio real de trabajo de ${p.titular}. Relevar recursos físicos, maquinarias, accesos y validar las expectativas para las 8 semanas.`,
    preguntas: [`¿Cuál es el corazón de ${p.nombre} hoy y qué es lo que más te enorgullece?`, '¿Con qué tiempo real contás por semana para trabajar en los entregables?'],
    objetivos: ['Completar la ficha de relevamiento inicial.', 'Identificar el principal cuello de botella operativo.'],
    check: ['Fotografiar instalaciones y equipamiento', 'Firmar carta de compromiso de participación', 'Validar disponibilidad horaria para los encuentros']
  });

  m.push({
    num: 2, f: `2026-09-${18 + offset}`, t: 'ind',
    lugar: 'Turismo', hora: '10:00 a 11:30',
    titulo: 'Propuesta de valor y segmentación de visitantes',
    asistentes: asist,
    guia: 'Definir con precisión el diferencial de la propuesta frente al resto de la oferta de Esquel y delimitar el perfil de cliente objetivo.',
    preguntas: ['¿Quién es tu cliente ideal: familias, parejas, aventureros, corporativo?', '¿Por qué te elegirían a vos frente a otra opción?'],
    objetivos: ['Redactar el texto de propuesta de valor en 3 párrafos.', 'Definir el público objetivo prioritario.'],
    check: ['Escribir la propuesta de valor diferenciada', 'Definir los 2 perfiles de visitante objetivo', 'Revisar antecedentes de ventas o consultas previas']
  });

  m.push({
    num: 3, f: `2026-09-${22 + offset}`, t: 'ind',
    lugar: 'Turismo', hora: '11:00 a 12:30',
    titulo: 'Estructura de costos, fijación de precios y márgenes',
    asistentes: asist,
    guia: 'Calcular el costo unitario por visitante o por pieza. Establecer tarifas que dejen margen comercial para comisionar a intermediarios.',
    preguntas: ['¿Cuánto te cuesta producir cada servicio/pieza incluyendo tu tiempo?', '¿Qué precio tolera el mercado actual?'],
    objetivos: ['Armar planilla de costos fijos y variables.', 'Fijar el precio al público y la tarifa neta para agencias.'],
    check: ['Calcular el costo por hora o por experiencia', 'Fijar precio de venta al público (PVP)', 'Establecer margen de comisión para intermediarios']
  });

  m.push({
    num: 4, f: '2026-09-30', t: 'gru',
    lugar: `Célula ${p.cel} (Turismo)`, hora: '09:30 a 12:00',
    titulo: `Puesta en común Célula ${p.cel}`,
    asistentes: [sr],
    guia: `Encuentro grupal de la Célula ${p.cel}. Poner en común avances, sinergias y resolver trabas compartidas.`,
    preguntas: ['¿Qué alianza podemos hacer con otro proyecto de la misma célula?'],
    objetivos: ['Generar al menos un cruce o paquete combinado entre proyectos.'],
    check: ['Llevar la planilla de costeo y precios', 'Presentar propuesta de sinergia con otro proyecto']
  });

  m.push({
    num: 5, f: `2026-10-${6 + offset}`, t: 'ind',
    lugar: (p.linea === 'Raíz' ? 'Predio' : 'Taller / Turismo'), hora: '10:00 a 11:30',
    titulo: 'Paquetización de la experiencia y guion del anfitrión',
    asistentes: asist,
    guia: 'Estructurar el paso a paso de la experiencia: bienvenida, nudo vivencial, despedida y momento de compra/recuerdo.',
    preguntas: ['¿Qué siente el visitante en los primeros 5 minutos al llegar?', '¿Cómo cerramos la experiencia para que recomiende y compre?'],
    objetivos: ['Escribir el guion de recepción y despedida.', 'Definir el objeto o souvenir conector.'],
    check: ['Redactar el guion del anfitrión paso a paso', 'Definir duración exacta y cupos por turno', 'Seleccionar el producto físico o recuerdo de cierre']
  });

  m.push({
    num: 6, f: `2026-10-${14 + offset}`, t: 'ind',
    lugar: 'Turismo', hora: '10:00 a 11:30',
    titulo: 'Canales de comercialización y alianzas locales',
    asistentes: asist,
    guia: 'Mapear los canales de venta: agencias receptivas, recepción de hoteles, comercios y venta digital.',
    preguntas: ['¿Qué prestadores de Esquel te pueden enviar pasajeros de forma regular?'],
    objetivos: ['Listar 5 prestadores aliados en Esquel y la comarca.', 'Armar modelo de acuerdo de derivación.'],
    check: ['Armar lista de 5 aliados comerciales estratégicos', 'Redactar ficha de producto para derivadores', 'Fijar protocolo de reserva y cancelación']
  });

  m.push({
    num: 7, f: `2026-10-${21 + offset}`, t: 'ind',
    lugar: 'Turismo', hora: '10:00 a 11:30',
    titulo: 'Comunicación digital, contenido y redes',
    asistentes: asist,
    guia: 'Optimizar la presencia digital y el material visual. Definir biografía, fotos de calidad y llamado a la acción claro.',
    preguntas: ['¿Tu perfil de Instagram o Google Maps dice claramente cómo comprar y cuánto cuesta?'],
    objetivos: ['Optimizar perfil de redes y fichas en Google Maps.', 'Definir calendario básico de publicaciones.'],
    check: ['Revisar bio de Instagram y botón de WhatsApp directo', 'Subir al menos 5 fotos de alta resolución', 'Crear o actualizar ficha de Google Maps']
  });

  m.push({
    num: 8, f: `2026-10-${28 + offset}`, t: (p.linea === 'Raíz' ? 'ter' : 'ind'),
    lugar: (p.linea === 'Raíz' ? 'Predio' : 'Taller / Local'), hora: '10:00 a 12:00',
    titulo: 'Ensayo piloto y validación con público de prueba',
    asistentes: asist,
    guia: 'Llevar adelante un ensayo general de la experiencia con un grupo reducido de prueba (consultores o invitados). Medir tiempos y ajustar.',
    preguntas: ['¿Qué salió según lo planeado y qué generó fricción o demoras?'],
    objetivos: ['Validar tiempos reales del guion.', 'Recoger feedback inmediato para correcciones.'],
    check: ['Realizar la prueba piloto completa con público invitado', 'Cronometrar cada etapa', 'Registrar devoluciones y puntos de mejora']
  });

  m.push({
    num: 9, f: `2026-11-${3 + offset}`, t: 'ind',
    lugar: 'Turismo', hora: '10:00 a 11:30',
    titulo: 'Ficha comercial definitiva y preparación del pitch',
    asistentes: asist,
    guia: 'Cerrar la carpeta comercial con fotos, precios y condiciones. Entrenar la presentación de 5 minutos para la rueda de negocios.',
    preguntas: ['¿Podés explicar tu producto y propuesta en 3 minutos con seguridad?'],
    objetivos: ['Dejar lista la ficha comercial impresa y digital.', 'Simular la reunión de negocios.'],
    check: ['Aprobar versión final de la ficha comercial', 'Practicar el pitch de 5 minutos', 'Definir las empresas con las que se reunirá en el cierre']
  });

  m.push({
    num: 10, f: '2026-11-10', t: 'cie',
    lugar: 'Acto de cierre', hora: '10:00 a 13:00',
    titulo: 'Distinción, resultados y rueda de negocios',
    asistentes: ['leandro', 'adria', 'mariela', 'francisco', 'agustina', 'cesia', 'noelia'],
    guia: 'Acto plenario final de la cohorte con autoridades, prensa y empresarios.',
    preguntas: ['¿Qué balance hacés del proceso y qué compromiso asumís para 2027?'],
    objetivos: ['Participar de la rueda de negocios y concretar acuerdos.'],
    check: ['Confirmar asistencia al acto', 'Llevar piezas y folletería para el stand', 'Cerrar al menos un contacto comercial en la rueda']
  });

  return m;
}

let php = `<?php
/**
 * Semilla y migración de datos para el Módulo de Gestión LAB.
 * Puebla consultores, proyectos y encuentros si las tablas están vacías.
 */

require_once __DIR__ . '/db.php';

function lab_asegurar_datos(PDO $pdo): void
{
    // 0. Asegurar usuarios del equipo técnico de Esquel LAB
    $equipoCuentas = [
        ['mariela',   'Mariela',   'editor', 'Mariela.Lab2026'],
        ['francisco', 'Francisco', 'editor', 'Francisco.Lab2026'],
        ['agustina',  'Agustina',  'editor', 'Agustina.Lab2026'],
        ['noelia',    'Noelia',    'editor', 'Noelia.Lab2026'],
        ['cesia',     'Cesia',     'editor', 'Cesia.Lab2026'],
    ];

    $insUser = $pdo->prepare("
        INSERT INTO users (username, password, role, must_change_password, created_at)
        VALUES (?, ?, ?, 0, datetime('now'))
    ");

    $checkUser = $pdo->prepare("SELECT 1 FROM users WHERE LOWER(username) = LOWER(?)");
    foreach ($equipoCuentas as $uRow) {
        $checkUser->execute([$uRow[0]]);
        if (!$checkUser->fetchColumn()) {
            $insUser->execute([
                $uRow[1],
                password_hash($uRow[3], PASSWORD_DEFAULT),
                $uRow[2]
            ]);
        }
    }

    // Asegurar acceso a Gestión LAB para todo el equipo consultor (conducción, seniors y juniors)
    $pdo->exec("
        INSERT OR IGNORE INTO lab_user_access (user_id, granted_by)
        SELECT id, 1 FROM users 
        WHERE LOWER(username) IN ('leandro', 'adria', 'mariela', 'francisco', 'agustina', 'noelia', 'cesia')
    ");

    // 1. Consultores
    $countCons = (int) $pdo->query("SELECT COUNT(*) FROM lab_consultores")->fetchColumn();
    if ($countCons === 0) {
        $insCons = $pdo->prepare("
            INSERT INTO lab_consultores (id, nombre, rol, disponibilidad, restricciones, color, activo)
            VALUES (?, ?, ?, ?, ?, ?, 1)
        ");
        $consultores = [
`;

for (const c of consultores) {
  php += `            ['${c.id}', ${JSON.stringify(c.nombre)}, '${c.rol}', ${JSON.stringify(JSON.stringify(c.disponibilidad))}, ${JSON.stringify(c.restricciones)}, '${c.color}'],\n`;
}

php += `        ];
        foreach ($consultores as $c) {
            $insCons->execute($c);
        }
    }

    // Definición de proyectos de la cohorte
    $proyectos = [
`;

for (const p of proyectos) {
  php += `            [
                '${p.id}',
                ${p.app_id},
                ${JSON.stringify(p.nombre)},
                ${JSON.stringify(p.titular)},
                '${p.linea}',
                ${p.puntaje},
                ${p.celula},
                '${p.sr}',
                '${p.jr}',
                ${JSON.stringify(JSON.stringify(p.diagnostico || []))},
                ${JSON.stringify(JSON.stringify(p.trabas || []))},
                ${JSON.stringify(JSON.stringify(p.ejes || []))},
                ${JSON.stringify(JSON.stringify(p.entregables || []))}
            ],\n`;
}

php += `        ];

    // 2. Proyectos
    $countProy = (int) $pdo->query("SELECT COUNT(*) FROM lab_proyectos")->fetchColumn();
    if ($countProy === 0) {
        $insProy = $pdo->prepare("
            INSERT INTO lab_proyectos (id, application_id, nombre, titular, linea, puntaje, celula, consultor_sr_id, consultor_jr_id, diagnostico, trabas, ejes, entregables)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $checkApp = $pdo->prepare("SELECT 1 FROM applications WHERE id = ?");
        foreach ($proyectos as $p) {
            $checkApp->execute([(int)$p[1]]);
            if (!$checkApp->fetchColumn()) {
                $p[1] = null;
            }
            $insProy->execute($p);
        }
    }

    // 3. Reuniones y Asistentes
    $countReu = (int) $pdo->query("SELECT COUNT(*) FROM lab_reuniones")->fetchColumn();
    if ($countReu === 0) {
        $insReu = $pdo->prepare("
            INSERT INTO lab_reuniones (
                id, proyecto_id, numero_reunion, titulo, tipo, lugar, fecha, hora_inicio, hora_fin,
                estado, guia_consultor, preguntas_clave, objetivos, checklist
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $insAsist = $pdo->prepare("
            INSERT OR IGNORE INTO lab_reunion_asistentes (reunion_id, consultor_id, rol)
            VALUES (?, ?, ?)
        ");

        $reuniones = [
`;


for (let idx = 0; idx < proyectos.length; idx++) {
  const p = proyectos[idx];
  const meetings = generateMeetingsFor(p, idx);
  for (const m of meetings) {
    const reuId = `${p.id}-${String(m.num).padStart(2, '0')}`;
    const horaParts = (m.hora || '').split(' a ');
    const horaInicio = horaParts[0] ? horaParts[0].trim() : '';
    const horaFin = horaParts[1] ? horaParts[1].trim() : '';
    const checkObjs = (m.check || []).map((txt, i) => ({ id: i + 1, texto: txt, done: false }));
    const asistList = m.asistentes || [p.sr, p.jr];

    php += `            [
                '${reuId}',
                '${p.id}',
                ${m.num},
                ${JSON.stringify(m.titulo)},
                '${m.t}',
                ${JSON.stringify(m.lugar || 'A confirmar')},
                '${m.f}',
                ${JSON.stringify(horaInicio)},
                ${JSON.stringify(horaFin)},
                'programada',
                ${JSON.stringify(m.guia || '')},
                ${JSON.stringify(JSON.stringify(m.preguntas || []))},
                ${JSON.stringify(JSON.stringify(m.objetivos || []))},
                ${JSON.stringify(JSON.stringify(checkObjs))},
                ${JSON.stringify(asistList)}
            ],\n`;
  }
}

php += `        ];

        $pdo->beginTransaction();
        foreach ($reuniones as $r) {
            $asistentes = array_pop($r);
            $insReu->execute($r);
            $reuId = $r[0];
            foreach ($asistentes as $consId) {
                $insAsist->execute([$reuId, $consId, 'asistente']);
            }
        }
        $pdo->commit();
    }

    // 4. Sincronización continua de proyectos y encuentros con planes curados
    $updProy = $pdo->prepare("
        UPDATE lab_proyectos 
        SET titular = ?, diagnostico = ?, trabas = ?, ejes = ?, entregables = ?
        WHERE id = ?
    ");
    $curados = ['crova', 'haiku', 'tambo', 'nire', 'corcho', 'vicotita', 'laberin', 'truepat', 'yamamori', 'senderos', 'carpint', 'arroyo', 'lucero', 'flypark', 'sabor', 'porota', 'margher'];
    foreach ($proyectos as $p) {
        if (in_array($p[0], $curados, true)) {
            $updProy->execute([$p[3], $p[9], $p[10], $p[11], $p[12], $p[0]]);
        }
    }

    // Sincronizar reuniones de Pausa de Ñire si todavía tiene la plantilla genérica inicial
    $checkNire = $pdo->query("SELECT titulo FROM lab_reuniones WHERE id = 'nire-01'")->fetchColumn();
    if ($checkNire === 'Diagnóstico en terreno y relevamiento de recursos' || !$checkNire) {
        $pdo->beginTransaction();
        $pdo->exec("DELETE FROM lab_reunion_asistentes WHERE reunion_id LIKE 'nire-%'");
        $pdo->exec("DELETE FROM lab_reuniones WHERE proyecto_id = 'nire'");

        $insReuNire = $pdo->prepare("
            INSERT INTO lab_reuniones (
                id, proyecto_id, numero_reunion, titulo, tipo, lugar, fecha, hora_inicio, hora_fin,
                estado, guia_consultor, preguntas_clave, objetivos, checklist
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $insAsistNire = $pdo->prepare("
            INSERT OR IGNORE INTO lab_reunion_asistentes (reunion_id, consultor_id, rol)
            VALUES (?, ?, ?)
        ");

        $reunionesNire = [
`;

const nireMeetingsList = exactMeetings.nire;
for (const m of nireMeetingsList) {
  const reuId = `nire-${String(m.num).padStart(2, '0')}`;
  const horaParts = (m.hora || '').split(' a ');
  const horaInicio = horaParts[0] ? horaParts[0].trim() : '';
  const horaFin = horaParts[1] ? horaParts[1].trim() : '';
  const checkObjs = (m.check || []).map((txt, i) => ({ id: i + 1, texto: txt, done: false }));
  const asistList = m.asistentes || ['adria', 'noelia'];

  php += `            [
                '${reuId}',
                'nire',
                ${m.num},
                ${JSON.stringify(m.titulo)},
                '${m.t}',
                ${JSON.stringify(m.lugar || 'A confirmar')},
                '${m.f}',
                ${JSON.stringify(horaInicio)},
                ${JSON.stringify(horaFin)},
                'programada',
                ${JSON.stringify(m.guia || '')},
                ${JSON.stringify(JSON.stringify(m.preguntas || []))},
                ${JSON.stringify(JSON.stringify(m.objetivos || []))},
                ${JSON.stringify(JSON.stringify(checkObjs))},
                ${JSON.stringify(asistList)}
            ],\n`;
}

php += `        ];

        foreach ($reunionesNire as $rn) {
            $asistentes = array_pop($rn);
            $insReuNire->execute($rn);
            $reuId = $rn[0];
            foreach ($asistentes as $consId) {
                $insAsistNire->execute([$reuId, $consId, 'asistente']);
            }
        }
        $pdo->commit();
    }

    // Sincronizar reuniones de Corcho Bikes si todavía tiene la plantilla genérica inicial
    $checkCorcho = $pdo->query("SELECT titulo FROM lab_reuniones WHERE id = 'corcho-01'")->fetchColumn();
    if ($checkCorcho === 'Diagnóstico en terreno y relevamiento de recursos' || !$checkCorcho) {
        $pdo->beginTransaction();
        $pdo->exec("DELETE FROM lab_reunion_asistentes WHERE reunion_id LIKE 'corcho-%'");
        $pdo->exec("DELETE FROM lab_reuniones WHERE proyecto_id = 'corcho'");

        $insReuCorcho = $pdo->prepare("
            INSERT INTO lab_reuniones (
                id, proyecto_id, numero_reunion, titulo, tipo, lugar, fecha, hora_inicio, hora_fin,
                estado, guia_consultor, preguntas_clave, objetivos, checklist
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $insAsistCorcho = $pdo->prepare("
            INSERT OR IGNORE INTO lab_reunion_asistentes (reunion_id, consultor_id, rol)
            VALUES (?, ?, ?)
        ");

        $reunionesCorcho = [
`;

const corchoMeetingsList = exactMeetings.corcho;
for (const m of corchoMeetingsList) {
  const reuId = `corcho-${String(m.num).padStart(2, '0')}`;
  const horaParts = (m.hora || '').split(' a ');
  const horaInicio = horaParts[0] ? horaParts[0].trim() : '';
  const horaFin = horaParts[1] ? horaParts[1].trim() : '';
  const checkObjs = (m.check || []).map((txt, i) => ({ id: i + 1, texto: txt, done: false }));
  const asistList = m.asistentes || ['leandro', 'agustina'];

  php += `            [
                '${reuId}',
                'corcho',
                ${m.num},
                ${JSON.stringify(m.titulo)},
                '${m.t}',
                ${JSON.stringify(m.lugar || 'A confirmar')},
                '${m.f}',
                ${JSON.stringify(horaInicio)},
                ${JSON.stringify(horaFin)},
                'programada',
                ${JSON.stringify(m.guia || '')},
                ${JSON.stringify(JSON.stringify(m.preguntas || []))},
                ${JSON.stringify(JSON.stringify(m.objetivos || []))},
                ${JSON.stringify(JSON.stringify(checkObjs))},
                ${JSON.stringify(asistList)}
            ],\n`;
}

php += `        ];

        foreach ($reunionesCorcho as $rc) {
            $asistentes = array_pop($rc);
            $insReuCorcho->execute($rc);
            $reuId = $rc[0];
            foreach ($asistentes as $consId) {
                $insAsistCorcho->execute([$reuId, $consId, 'asistente']);
            }
        }
        $pdo->commit();
    }

    // Sincronizar reuniones de Vico + Tita si todavía tiene la plantilla genérica inicial
    $checkVicotita = $pdo->query("SELECT titulo FROM lab_reuniones WHERE id = 'vicotita-01'")->fetchColumn();
    if ($checkVicotita === 'Diagnóstico en terreno y relevamiento de recursos' || !$checkVicotita) {
        $pdo->beginTransaction();
        $pdo->exec("DELETE FROM lab_reunion_asistentes WHERE reunion_id LIKE 'vicotita-%'");
        $pdo->exec("DELETE FROM lab_reuniones WHERE proyecto_id = 'vicotita'");

        $insReuVicotita = $pdo->prepare("
            INSERT INTO lab_reuniones (
                id, proyecto_id, numero_reunion, titulo, tipo, lugar, fecha, hora_inicio, hora_fin,
                estado, guia_consultor, preguntas_clave, objetivos, checklist
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $insAsistVicotita = $pdo->prepare("
            INSERT OR IGNORE INTO lab_reunion_asistentes (reunion_id, consultor_id, rol)
            VALUES (?, ?, ?)
        ");

        $reunionesVicotita = [
`;

const vicotitaMeetingsList = exactMeetings.vicotita;
for (const m of vicotitaMeetingsList) {
  const reuId = `vicotita-${String(m.num).padStart(2, '0')}`;
  const horaParts = (m.hora || '').split(' a ');
  const horaInicio = horaParts[0] ? horaParts[0].trim() : '';
  const horaFin = horaParts[1] ? horaParts[1].trim() : '';
  const checkObjs = (m.check || []).map((txt, i) => ({ id: i + 1, texto: txt, done: false }));
  const asistList = m.asistentes || ['mariela', 'cesia'];

  php += `            [
                '${reuId}',
                'vicotita',
                ${m.num},
                ${JSON.stringify(m.titulo)},
                '${m.t}',
                ${JSON.stringify(m.lugar || 'A confirmar')},
                '${m.f}',
                ${JSON.stringify(horaInicio)},
                ${JSON.stringify(horaFin)},
                'programada',
                ${JSON.stringify(m.guia || '')},
                ${JSON.stringify(JSON.stringify(m.preguntas || []))},
                ${JSON.stringify(JSON.stringify(m.objetivos || []))},
                ${JSON.stringify(JSON.stringify(checkObjs))},
                ${JSON.stringify(asistList)}
            ],\n`;
}

php += `        ];

        foreach ($reunionesVicotita as $rv) {
            $asistentes = array_pop($rv);
            $insReuVicotita->execute($rv);
            $reuId = $rv[0];
            foreach ($asistentes as $consId) {
                $insAsistVicotita->execute([$reuId, $consId, 'asistente']);
            }
        }
        $pdo->commit();
    }

    // Sincronizar reuniones de El secreto del laberinto si todavía tiene la plantilla genérica inicial
    $checkLaberin = $pdo->query("SELECT titulo FROM lab_reuniones WHERE id = 'laberin-01'")->fetchColumn();
    if ($checkLaberin === 'Diagnóstico en terreno y relevamiento de recursos' || !$checkLaberin) {
        $pdo->beginTransaction();
        $pdo->exec("DELETE FROM lab_reunion_asistentes WHERE reunion_id LIKE 'laberin-%'");
        $pdo->exec("DELETE FROM lab_reuniones WHERE proyecto_id = 'laberin'");

        $insReuLaberin = $pdo->prepare("
            INSERT INTO lab_reuniones (
                id, proyecto_id, numero_reunion, titulo, tipo, lugar, fecha, hora_inicio, hora_fin,
                estado, guia_consultor, preguntas_clave, objetivos, checklist
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $insAsistLaberin = $pdo->prepare("
            INSERT OR IGNORE INTO lab_reunion_asistentes (reunion_id, consultor_id, rol)
            VALUES (?, ?, ?)
        ");

        $reunionesLaberin = [
`;

const laberinMeetingsList = exactMeetings.laberin;
for (const m of laberinMeetingsList) {
  const reuId = `laberin-${String(m.num).padStart(2, '0')}`;
  const horaParts = (m.hora || '').split(' a ');
  const horaInicio = horaParts[0] ? horaParts[0].trim() : '';
  const horaFin = horaParts[1] ? horaParts[1].trim() : '';
  const checkObjs = (m.check || []).map((txt, i) => ({ id: i + 1, texto: txt, done: false }));
  const asistList = m.asistentes || ['adria', 'noelia'];

  php += `            [
                '${reuId}',
                'laberin',
                ${m.num},
                ${JSON.stringify(m.titulo)},
                '${m.t}',
                ${JSON.stringify(m.lugar || 'A confirmar')},
                '${m.f}',
                ${JSON.stringify(horaInicio)},
                ${JSON.stringify(horaFin)},
                'programada',
                ${JSON.stringify(m.guia || '')},
                ${JSON.stringify(JSON.stringify(m.preguntas || []))},
                ${JSON.stringify(JSON.stringify(m.objetivos || []))},
                ${JSON.stringify(JSON.stringify(checkObjs))},
                ${JSON.stringify(asistList)}
            ],\n`;
}

php += `        ];

        foreach ($reunionesLaberin as $rl) {
            $asistentes = array_pop($rl);
            $insReuLaberin->execute($rl);
            $reuId = $rl[0];
            foreach ($asistentes as $consId) {
                $insAsistLaberin->execute([$reuId, $consId, 'asistente']);
            }
        }
        $pdo->commit();
    }

    // Sincronizar reuniones de True Patagonia (Inmersivos Textiles) si todavía tiene la plantilla genérica inicial
    $checkTruepat = $pdo->query("SELECT titulo FROM lab_reuniones WHERE id = 'truepat-01'")->fetchColumn();
    if ($checkTruepat === 'Diagnóstico en terreno y relevamiento de recursos' || !$checkTruepat) {
        $pdo->beginTransaction();
        $pdo->exec("DELETE FROM lab_reunion_asistentes WHERE reunion_id LIKE 'truepat-%'");
        $pdo->exec("DELETE FROM lab_reuniones WHERE proyecto_id = 'truepat'");

        $insReuTruepat = $pdo->prepare("
            INSERT INTO lab_reuniones (
                id, proyecto_id, numero_reunion, titulo, tipo, lugar, fecha, hora_inicio, hora_fin,
                estado, guia_consultor, preguntas_clave, objetivos, checklist
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $insAsistTruepat = $pdo->prepare("
            INSERT OR IGNORE INTO lab_reunion_asistentes (reunion_id, consultor_id, rol)
            VALUES (?, ?, ?)
        ");

        $reunionesTruepat = [
`;

const truepatMeetingsList = exactMeetings.truepat;
for (const m of truepatMeetingsList) {
  const reuId = `truepat-${String(m.num).padStart(2, '0')}`;
  const horaParts = (m.hora || '').split(' a ');
  const horaInicio = horaParts[0] ? horaParts[0].trim() : '';
  const horaFin = horaParts[1] ? horaParts[1].trim() : '';
  const checkObjs = (m.check || []).map((txt, i) => ({ id: i + 1, texto: txt, done: false }));
  const asistList = m.asistentes || ['mariela', 'noelia'];

  php += `            [
                '${reuId}',
                'truepat',
                ${m.num},
                ${JSON.stringify(m.titulo)},
                '${m.t}',
                ${JSON.stringify(m.lugar || 'A confirmar')},
                '${m.f}',
                ${JSON.stringify(horaInicio)},
                ${JSON.stringify(horaFin)},
                'programada',
                ${JSON.stringify(m.guia || '')},
                ${JSON.stringify(JSON.stringify(m.preguntas || []))},
                ${JSON.stringify(JSON.stringify(m.objetivos || []))},
                ${JSON.stringify(JSON.stringify(checkObjs))},
                ${JSON.stringify(asistList)}
            ],\n`;
}

php += `        ];

        foreach ($reunionesTruepat as $rt) {
            $asistentes = array_pop($rt);
            $insReuTruepat->execute($rt);
            $reuId = $rt[0];
            foreach ($asistentes as $consId) {
                $insAsistTruepat->execute([$reuId, $consId, 'asistente']);
            }
        }
        $pdo->commit();
    }

    // Sincronizar reuniones de Yamamori Travel si todavía tiene la plantilla genérica inicial
    $checkYamamori = $pdo->query("SELECT titulo FROM lab_reuniones WHERE id = 'yamamori-01'")->fetchColumn();
    if ($checkYamamori === 'Diagnóstico en terreno y relevamiento de recursos' || !$checkYamamori) {
        $pdo->beginTransaction();
        $pdo->exec("DELETE FROM lab_reunion_asistentes WHERE reunion_id LIKE 'yamamori-%'");
        $pdo->exec("DELETE FROM lab_reuniones WHERE proyecto_id = 'yamamori'");

        $insReuYamamori = $pdo->prepare("
            INSERT INTO lab_reuniones (
                id, proyecto_id, numero_reunion, titulo, tipo, lugar, fecha, hora_inicio, hora_fin,
                estado, guia_consultor, preguntas_clave, objetivos, checklist
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $insAsistYamamori = $pdo->prepare("
            INSERT OR IGNORE INTO lab_reunion_asistentes (reunion_id, consultor_id, rol)
            VALUES (?, ?, ?)
        ");

        $reunionesYamamori = [
`;

const yamamoriMeetingsList = exactMeetings.yamamori;
for (const m of yamamoriMeetingsList) {
  const reuId = `yamamori-${String(m.num).padStart(2, '0')}`;
  const horaParts = (m.hora || '').split(' a ');
  const horaInicio = horaParts[0] ? horaParts[0].trim() : '';
  const horaFin = horaParts[1] ? horaParts[1].trim() : '';
  const checkObjs = (m.check || []).map((txt, i) => ({ id: i + 1, texto: txt, done: false }));
  const asistList = m.asistentes || ['mariela', 'agustina'];

  php += `            [
                '${reuId}',
                'yamamori',
                ${m.num},
                ${JSON.stringify(m.titulo)},
                '${m.t}',
                ${JSON.stringify(m.lugar || 'A confirmar')},
                '${m.f}',
                ${JSON.stringify(horaInicio)},
                ${JSON.stringify(horaFin)},
                'programada',
                ${JSON.stringify(m.guia || '')},
                ${JSON.stringify(JSON.stringify(m.preguntas || []))},
                ${JSON.stringify(JSON.stringify(m.objetivos || []))},
                ${JSON.stringify(JSON.stringify(checkObjs))},
                ${JSON.stringify(asistList)}
            ],\n`;
}

php += `        ];

        foreach ($reunionesYamamori as $ry) {
            $asistentes = array_pop($ry);
            $insReuYamamori->execute($ry);
            $reuId = $ry[0];
            foreach ($asistentes as $consId) {
                $insAsistYamamori->execute([$reuId, $consId, 'asistente']);
            }
        }
        $pdo->commit();
    }

    // Sincronizar reuniones de Senderos con Identidad si todavía tiene la plantilla genérica inicial
    $checkSenderos = $pdo->query("SELECT titulo FROM lab_reuniones WHERE id = 'senderos-01'")->fetchColumn();
    if ($checkSenderos === 'Diagnóstico en terreno y relevamiento de recursos' || !$checkSenderos) {
        $pdo->beginTransaction();
        $pdo->exec("DELETE FROM lab_reunion_asistentes WHERE reunion_id LIKE 'senderos-%'");
        $pdo->exec("DELETE FROM lab_reuniones WHERE proyecto_id = 'senderos'");

        $insReuSenderos = $pdo->prepare("
            INSERT INTO lab_reuniones (
                id, proyecto_id, numero_reunion, titulo, tipo, lugar, fecha, hora_inicio, hora_fin,
                estado, guia_consultor, preguntas_clave, objetivos, checklist
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $insAsistSenderos = $pdo->prepare("
            INSERT OR IGNORE INTO lab_reunion_asistentes (reunion_id, consultor_id, rol)
            VALUES (?, ?, ?)
        ");

        $reunionesSenderos = [
`;

const senderosMeetingsList = exactMeetings.senderos;
for (const m of senderosMeetingsList) {
  const reuId = `senderos-${String(m.num).padStart(2, '0')}`;
  const horaParts = (m.hora || '').split(' a ');
  const horaInicio = horaParts[0] ? horaParts[0].trim() : '';
  const horaFin = horaParts[1] ? horaParts[1].trim() : '';
  const checkObjs = (m.check || []).map((txt, i) => ({ id: i + 1, texto: txt, done: false }));
  const asistList = m.asistentes || ['adria', 'francisco'];

  php += `            [
                '${reuId}',
                'senderos',
                ${m.num},
                ${JSON.stringify(m.titulo)},
                '${m.t}',
                ${JSON.stringify(m.lugar || 'A confirmar')},
                '${m.f}',
                ${JSON.stringify(horaInicio)},
                ${JSON.stringify(horaFin)},
                'programada',
                ${JSON.stringify(m.guia || '')},
                ${JSON.stringify(JSON.stringify(m.preguntas || []))},
                ${JSON.stringify(JSON.stringify(m.objetivos || []))},
                ${JSON.stringify(JSON.stringify(checkObjs))},
                ${JSON.stringify(asistList)}
            ],\n`;
}

php += `        ];

        foreach ($reunionesSenderos as $rs) {
            $asistentes = array_pop($rs);
            $insReuSenderos->execute($rs);
            $reuId = $rs[0];
            foreach ($asistentes as $consId) {
                $insAsistSenderos->execute([$reuId, $consId, 'asistente']);
            }
        }
        $pdo->commit();
    }

    // Sincronizar reuniones de Carpintero Esquel si todavía tiene la plantilla genérica inicial
    $checkCarpint = $pdo->query("SELECT titulo FROM lab_reuniones WHERE id = 'carpint-01'")->fetchColumn();
    if ($checkCarpint === 'Diagnóstico en terreno y relevamiento de recursos' || !$checkCarpint) {
        $pdo->beginTransaction();
        $pdo->exec("DELETE FROM lab_reunion_asistentes WHERE reunion_id LIKE 'carpint-%'");
        $pdo->exec("DELETE FROM lab_reuniones WHERE proyecto_id = 'carpint'");

        $insReuCarpint = $pdo->prepare("
            INSERT INTO lab_reuniones (
                id, proyecto_id, numero_reunion, titulo, tipo, lugar, fecha, hora_inicio, hora_fin,
                estado, guia_consultor, preguntas_clave, objetivos, checklist
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $insAsistCarpint = $pdo->prepare("
            INSERT OR IGNORE INTO lab_reunion_asistentes (reunion_id, consultor_id, rol)
            VALUES (?, ?, ?)
        ");

        $reunionesCarpint = [
`;

const carpintMeetingsList = exactMeetings.carpint;
for (const m of carpintMeetingsList) {
  const reuId = `carpint-${String(m.num).padStart(2, '0')}`;
  const horaParts = (m.hora || '').split(' a ');
  const horaInicio = horaParts[0] ? horaParts[0].trim() : '';
  const horaFin = horaParts[1] ? horaParts[1].trim() : '';
  const checkObjs = (m.check || []).map((txt, i) => ({ id: i + 1, texto: txt, done: false }));
  const asistList = m.asistentes || ['mariela', 'cesia'];

  php += `            [
                '${reuId}',
                'carpint',
                ${m.num},
                ${JSON.stringify(m.titulo)},
                '${m.t}',
                ${JSON.stringify(m.lugar || 'A confirmar')},
                '${m.f}',
                ${JSON.stringify(horaInicio)},
                ${JSON.stringify(horaFin)},
                'programada',
                ${JSON.stringify(m.guia || '')},
                ${JSON.stringify(JSON.stringify(m.preguntas || []))},
                ${JSON.stringify(JSON.stringify(m.objetivos || []))},
                ${JSON.stringify(JSON.stringify(checkObjs))},
                ${JSON.stringify(asistList)}
            ],\n`;
}

php += `        ];

        foreach ($reunionesCarpint as $rc) {
            $asistentes = array_pop($rc);
            $insReuCarpint->execute($rc);
            $reuId = $rc[0];
            foreach ($asistentes as $consId) {
                $insAsistCarpint->execute([$reuId, $consId, 'asistente']);
            }
        }
        $pdo->commit();
    }

    // Sincronizar reuniones de El Arroyo Que Nos Ve Crecer si todavía tiene la plantilla genérica inicial
    $checkArroyo = $pdo->query("SELECT titulo FROM lab_reuniones WHERE id = 'arroyo-01'")->fetchColumn();
    if ($checkArroyo === 'Diagnóstico en terreno y relevamiento de recursos' || !$checkArroyo) {
        $pdo->beginTransaction();
        $pdo->exec("DELETE FROM lab_reunion_asistentes WHERE reunion_id LIKE 'arroyo-%'");
        $pdo->exec("DELETE FROM lab_reuniones WHERE proyecto_id = 'arroyo'");

        $insReuArroyo = $pdo->prepare("
            INSERT INTO lab_reuniones (
                id, proyecto_id, numero_reunion, titulo, tipo, lugar, fecha, hora_inicio, hora_fin,
                estado, guia_consultor, preguntas_clave, objetivos, checklist
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $insAsistArroyo = $pdo->prepare("
            INSERT OR IGNORE INTO lab_reunion_asistentes (reunion_id, consultor_id, rol)
            VALUES (?, ?, ?)
        ");

        $reunionesArroyo = [
`;

const arroyoMeetingsList = exactMeetings.arroyo;
for (const m of arroyoMeetingsList) {
  const reuId = `arroyo-${String(m.num).padStart(2, '0')}`;
  const horaParts = (m.hora || '').split(' a ');
  const horaInicio = horaParts[0] ? horaParts[0].trim() : '';
  const horaFin = horaParts[1] ? horaParts[1].trim() : '';
  const checkObjs = (m.check || []).map((txt, i) => ({ id: i + 1, texto: txt, done: false }));
  const asistList = m.asistentes || ['adria', 'francisco'];

  php += `            [
                '${reuId}',
                'arroyo',
                ${m.num},
                ${JSON.stringify(m.titulo)},
                '${m.t}',
                ${JSON.stringify(m.lugar || 'A confirmar')},
                '${m.f}',
                ${JSON.stringify(horaInicio)},
                ${JSON.stringify(horaFin)},
                'programada',
                ${JSON.stringify(m.guia || '')},
                ${JSON.stringify(JSON.stringify(m.preguntas || []))},
                ${JSON.stringify(JSON.stringify(m.objetivos || []))},
                ${JSON.stringify(JSON.stringify(checkObjs))},
                ${JSON.stringify(asistList)}
            ],\n`;
}

php += `        ];

        foreach ($reunionesArroyo as $ra) {
            $asistentes = array_pop($ra);
            $insReuArroyo->execute($ra);
            $reuId = $ra[0];
            foreach ($asistentes as $consId) {
                $insAsistArroyo->execute([$reuId, $consId, 'asistente']);
            }
        }
        $pdo->commit();
    }

    // Sincronizar reuniones de Centro Integral Ecuestre Lucero si todavía tiene la plantilla genérica inicial
    $checkLucero = $pdo->query("SELECT titulo FROM lab_reuniones WHERE id = 'lucero-01'")->fetchColumn();
    if ($checkLucero === 'Diagnóstico en terreno y relevamiento de recursos' || !$checkLucero) {
        $pdo->beginTransaction();
        $pdo->exec("DELETE FROM lab_reunion_asistentes WHERE reunion_id LIKE 'lucero-%'");
        $pdo->exec("DELETE FROM lab_reuniones WHERE proyecto_id = 'lucero'");

        $insReuLucero = $pdo->prepare("
            INSERT INTO lab_reuniones (
                id, proyecto_id, numero_reunion, titulo, tipo, lugar, fecha, hora_inicio, hora_fin,
                estado, guia_consultor, preguntas_clave, objetivos, checklist
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $insAsistLucero = $pdo->prepare("
            INSERT OR IGNORE INTO lab_reunion_asistentes (reunion_id, consultor_id, rol)
            VALUES (?, ?, ?)
        ");

        $reunionesLucero = [
`;

const luceroMeetingsList = exactMeetings.lucero;
for (const m of luceroMeetingsList) {
  const reuId = `lucero-${String(m.num).padStart(2, '0')}`;
  const horaParts = (m.hora || '').split(' a ');
  const horaInicio = horaParts[0] ? horaParts[0].trim() : '';
  const horaFin = horaParts[1] ? horaParts[1].trim() : '';
  const checkObjs = (m.check || []).map((txt, i) => ({ id: i + 1, texto: txt, done: false }));
  const asistList = m.asistentes || ['adria', 'noelia'];

  php += `            [
                '${reuId}',
                'lucero',
                ${m.num},
                ${JSON.stringify(m.titulo)},
                '${m.t}',
                ${JSON.stringify(m.lugar || 'A confirmar')},
                '${m.f}',
                ${JSON.stringify(horaInicio)},
                ${JSON.stringify(horaFin)},
                'programada',
                ${JSON.stringify(m.guia || '')},
                ${JSON.stringify(JSON.stringify(m.preguntas || []))},
                ${JSON.stringify(JSON.stringify(m.objetivos || []))},
                ${JSON.stringify(JSON.stringify(checkObjs))},
                ${JSON.stringify(asistList)}
            ],\n`;
}

php += `        ];

        foreach ($reunionesLucero as $rl) {
            $asistentes = array_pop($rl);
            $insReuLucero->execute($rl);
            $reuId = $rl[0];
            foreach ($asistentes as $consId) {
                $insAsistLucero->execute([$reuId, $consId, 'asistente']);
            }
        }
        $pdo->commit();
    }

    // Sincronizar reuniones de FLYPARK si todavía tiene la plantilla genérica inicial
    $checkFlypark = $pdo->query("SELECT titulo FROM lab_reuniones WHERE id = 'flypark-01'")->fetchColumn();
    if ($checkFlypark === 'Diagnóstico en terreno y relevamiento de recursos' || !$checkFlypark) {
        $pdo->beginTransaction();
        $pdo->exec("DELETE FROM lab_reunion_asistentes WHERE reunion_id LIKE 'flypark-%'");
        $pdo->exec("DELETE FROM lab_reuniones WHERE proyecto_id = 'flypark'");

        $insReuFlypark = $pdo->prepare("
            INSERT INTO lab_reuniones (
                id, proyecto_id, numero_reunion, titulo, tipo, lugar, fecha, hora_inicio, hora_fin,
                estado, guia_consultor, preguntas_clave, objetivos, checklist
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $insAsistFlypark = $pdo->prepare("
            INSERT OR IGNORE INTO lab_reunion_asistentes (reunion_id, consultor_id, rol)
            VALUES (?, ?, ?)
        ");

        $reunionesFlypark = [
`;

const flyparkMeetingsList = exactMeetings.flypark;
for (const m of flyparkMeetingsList) {
  const reuId = `flypark-${String(m.num).padStart(2, '0')}`;
  const horaParts = (m.hora || '').split(' a ');
  const horaInicio = horaParts[0] ? horaParts[0].trim() : '';
  const horaFin = horaParts[1] ? horaParts[1].trim() : '';
  const checkObjs = (m.check || []).map((txt, i) => ({ id: i + 1, texto: txt, done: false }));
  const asistList = m.asistentes || ['leandro', 'agustina'];

  php += `            [
                '${reuId}',
                'flypark',
                ${m.num},
                ${JSON.stringify(m.titulo)},
                '${m.t}',
                ${JSON.stringify(m.lugar || 'A confirmar')},
                '${m.f}',
                ${JSON.stringify(horaInicio)},
                ${JSON.stringify(horaFin)},
                'programada',
                ${JSON.stringify(m.guia || '')},
                ${JSON.stringify(JSON.stringify(m.preguntas || []))},
                ${JSON.stringify(JSON.stringify(m.objetivos || []))},
                ${JSON.stringify(JSON.stringify(checkObjs))},
                ${JSON.stringify(asistList)}
            ],\n`;
}

php += `        ];

        foreach ($reunionesFlypark as $rf) {
            $asistentes = array_pop($rf);
            $insReuFlypark->execute($rf);
            $reuId = $rf[0];
            foreach ($asistentes as $consId) {
                $insAsistFlypark->execute([$reuId, $consId, 'asistente']);
            }
        }
        $pdo->commit();
    }

    // Sincronizar reuniones de Sabor Mapuche si todavía tiene la plantilla genérica inicial
    $checkSabor = $pdo->query("SELECT titulo FROM lab_reuniones WHERE id = 'sabor-01'")->fetchColumn();
    if ($checkSabor === 'Diagnóstico en terreno y relevamiento de recursos' || !$checkSabor) {
        $pdo->beginTransaction();
        $pdo->exec("DELETE FROM lab_reunion_asistentes WHERE reunion_id LIKE 'sabor-%'");
        $pdo->exec("DELETE FROM lab_reuniones WHERE proyecto_id = 'sabor'");

        $insReuSabor = $pdo->prepare("
            INSERT INTO lab_reuniones (
                id, proyecto_id, numero_reunion, titulo, tipo, lugar, fecha, hora_inicio, hora_fin,
                estado, guia_consultor, preguntas_clave, objetivos, checklist
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $insAsistSabor = $pdo->prepare("
            INSERT OR IGNORE INTO lab_reunion_asistentes (reunion_id, consultor_id, rol)
            VALUES (?, ?, ?)
        ");

        $reunionesSabor = [
`;

const saborMeetingsList = exactMeetings.sabor;
for (const m of saborMeetingsList) {
  const reuId = `sabor-${String(m.num).padStart(2, '0')}`;
  const horaParts = (m.hora || '').split(' a ');
  const horaInicio = horaParts[0] ? horaParts[0].trim() : '';
  const horaFin = horaParts[1] ? horaParts[1].trim() : '';
  const checkObjs = (m.check || []).map((txt, i) => ({ id: i + 1, texto: txt, done: false }));
  const asistList = m.asistentes || ['adria', 'francisco'];

  php += `            [
                '${reuId}',
                'sabor',
                ${m.num},
                ${JSON.stringify(m.titulo)},
                '${m.t}',
                ${JSON.stringify(m.lugar || 'A confirmar')},
                '${m.f}',
                ${JSON.stringify(horaInicio)},
                ${JSON.stringify(horaFin)},
                'programada',
                ${JSON.stringify(m.guia || '')},
                ${JSON.stringify(JSON.stringify(m.preguntas || []))},
                ${JSON.stringify(JSON.stringify(m.objetivos || []))},
                ${JSON.stringify(JSON.stringify(checkObjs))},
                ${JSON.stringify(asistList)}
            ],\n`;
}

php += `        ];

        foreach ($reunionesSabor as $rs) {
            $asistentes = array_pop($rs);
            $insReuSabor->execute($rs);
            $reuId = $rs[0];
            foreach ($asistentes as $consId) {
                $insAsistSabor->execute([$reuId, $consId, 'asistente']);
            }
        }
        $pdo->commit();
    }

    // Sincronizar reuniones de Lo de Porota si todavía tiene la plantilla genérica inicial
    $checkPorota = $pdo->query("SELECT titulo FROM lab_reuniones WHERE id = 'porota-01'")->fetchColumn();
    if ($checkPorota === 'Diagnóstico en terreno y relevamiento de recursos' || !$checkPorota) {
        $pdo->beginTransaction();
        $pdo->exec("DELETE FROM lab_reunion_asistentes WHERE reunion_id LIKE 'porota-%'");
        $pdo->exec("DELETE FROM lab_reuniones WHERE proyecto_id = 'porota'");

        $insReuPorota = $pdo->prepare("
            INSERT INTO lab_reuniones (
                id, proyecto_id, numero_reunion, titulo, tipo, lugar, fecha, hora_inicio, hora_fin,
                estado, guia_consultor, preguntas_clave, objetivos, checklist
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $insAsistPorota = $pdo->prepare("
            INSERT OR IGNORE INTO lab_reunion_asistentes (reunion_id, consultor_id, rol)
            VALUES (?, ?, ?)
        ");

        $reunionesPorota = [
`;

const porotaMeetingsList = exactMeetings.porota;
for (const m of porotaMeetingsList) {
  const reuId = `porota-${String(m.num).padStart(2, '0')}`;
  const horaParts = (m.hora || '').split(' a ');
  const horaInicio = horaParts[0] ? horaParts[0].trim() : '';
  const horaFin = horaParts[1] ? horaParts[1].trim() : '';
  const checkObjs = (m.check || []).map((txt, i) => ({ id: i + 1, texto: txt, done: false }));
  const asistList = m.asistentes || ['adria', 'francisco'];

  php += `            [
                '${reuId}',
                'porota',
                ${m.num},
                ${JSON.stringify(m.titulo)},
                '${m.t}',
                ${JSON.stringify(m.lugar || 'A confirmar')},
                '${m.f}',
                ${JSON.stringify(horaInicio)},
                ${JSON.stringify(horaFin)},
                'programada',
                ${JSON.stringify(m.guia || '')},
                ${JSON.stringify(JSON.stringify(m.preguntas || []))},
                ${JSON.stringify(JSON.stringify(m.objetivos || []))},
                ${JSON.stringify(JSON.stringify(checkObjs))},
                ${JSON.stringify(asistList)}
            ],\n`;
}

php += `        ];

        foreach ($reunionesPorota as $rp) {
            $asistentes = array_pop($rp);
            $insReuPorota->execute($rp);
            $reuId = $rp[0];
            foreach ($asistentes as $consId) {
                $insAsistPorota->execute([$reuId, $consId, 'asistente']);
            }
        }
        $pdo->commit();
    }

    // Sincronizar reuniones de Establecimiento Margherita si todavía tiene la plantilla genérica inicial
    $checkMargher = $pdo->query("SELECT titulo FROM lab_reuniones WHERE id = 'margher-01'")->fetchColumn();
    if ($checkMargher === 'Diagnóstico en terreno y relevamiento de recursos' || !$checkMargher) {
        $pdo->beginTransaction();
        $pdo->exec("DELETE FROM lab_reunion_asistentes WHERE reunion_id LIKE 'margher-%'");
        $pdo->exec("DELETE FROM lab_reuniones WHERE proyecto_id = 'margher'");

        $insReuMargher = $pdo->prepare("
            INSERT INTO lab_reuniones (
                id, proyecto_id, numero_reunion, titulo, tipo, lugar, fecha, hora_inicio, hora_fin,
                estado, guia_consultor, preguntas_clave, objetivos, checklist
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $insAsistMargher = $pdo->prepare("
            INSERT OR IGNORE INTO lab_reunion_asistentes (reunion_id, consultor_id, rol)
            VALUES (?, ?, ?)
        ");

        $reunionesMargher = [
`;

const margherMeetingsList = exactMeetings.margher;
for (const m of margherMeetingsList) {
  const reuId = `margher-${String(m.num).padStart(2, '0')}`;
  const horaParts = (m.hora || '').split(' a ');
  const horaInicio = horaParts[0] ? horaParts[0].trim() : '';
  const horaFin = horaParts[1] ? horaParts[1].trim() : '';
  const checkObjs = (m.check || []).map((txt, i) => ({ id: i + 1, texto: txt, done: false }));
  const asistList = m.asistentes || ['adria', 'noelia'];

  php += `            [
                '${reuId}',
                'margher',
                ${m.num},
                ${JSON.stringify(m.titulo)},
                '${m.t}',
                ${JSON.stringify(m.lugar || 'A confirmar')},
                '${m.f}',
                ${JSON.stringify(horaInicio)},
                ${JSON.stringify(horaFin)},
                'programada',
                ${JSON.stringify(m.guia || '')},
                ${JSON.stringify(JSON.stringify(m.preguntas || []))},
                ${JSON.stringify(JSON.stringify(m.objetivos || []))},
                ${JSON.stringify(JSON.stringify(checkObjs))},
                ${JSON.stringify(asistList)}
            ],\n`;
}

php += `        ];

        foreach ($reunionesMargher as $rm) {
            $asistentes = array_pop($rm);
            $insReuMargher->execute($rm);
            $reuId = $rm[0];
            foreach ($asistentes as $consId) {
                $insAsistMargher->execute([$reuId, $consId, 'asistente']);
            }
        }
        $pdo->commit();
    }
}
`;

fs.writeFileSync(path.join(__dirname, '../includes/lab_seed.php'), php, 'utf8');
console.log('includes/lab_seed.php written successfully!');

