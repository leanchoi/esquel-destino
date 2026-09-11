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

    // 2. Proyectos
    $countProy = (int) $pdo->query("SELECT COUNT(*) FROM lab_proyectos")->fetchColumn();
    if ($countProy === 0) {
        $insProy = $pdo->prepare("
            INSERT INTO lab_proyectos (id, application_id, nombre, titular, linea, puntaje, celula, consultor_sr_id, consultor_jr_id, diagnostico, trabas, ejes, entregables)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
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
}
`;

fs.writeFileSync(path.join(__dirname, '../includes/lab_seed.php'), php, 'utf8');
console.log('includes/lab_seed.php written successfully!');
