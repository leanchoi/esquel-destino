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
    $curados = ['crova', 'haiku', 'tambo', 'nire', 'corcho', 'vicotita', 'laberin', 'truepat'];
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
}
`;

fs.writeFileSync(path.join(__dirname, '../includes/lab_seed.php'), php, 'utf8');
console.log('includes/lab_seed.php written successfully!');

