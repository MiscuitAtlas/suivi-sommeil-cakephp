// Comportements de l'interface. Aucun script en ligne : compatible avec la politique de sécurité du site.
(function () {
  'use strict';

  // Confirmation avant une action irréversible : <form data-confirm="…">.
  document.addEventListener('submit', function (event) {
    var form = event.target;
    var message = form.getAttribute && form.getAttribute('data-confirm');
    if (message && !window.confirm(message)) {
      event.preventDefault();
    }
  });

  // Heures de réveil qui tombent en fin de cycle, d'après l'heure du coucher.
  var bedtime = document.querySelector('[data-bedtime]');
  var output = document.querySelector('[data-wake-suggestions]');
  if (!bedtime || !output) {
    return;
  }
  var cycleMinutes = 90;
  function format(minutes) {
    minutes = ((minutes % 1440) + 1440) % 1440;
    var h = Math.floor(minutes / 60);
    var m = minutes % 60;
    return (h < 10 ? '0' : '') + h + ':' + (m < 10 ? '0' : '') + m;
  }
  function update() {
    var match = /^(\d{1,2}):(\d{2})/.exec(bedtime.value);
    if (!match) {
      output.hidden = true;
      return;
    }
    var start = parseInt(match[1], 10) * 60 + parseInt(match[2], 10);
    output.textContent =
      'Couché à ' + format(start) + ', vous vous réveillerez en fin de cycle à ' +
      format(start + 5 * cycleMinutes) + ' (5 cycles) ou à ' + format(start + 6 * cycleMinutes) + ' (6 cycles).';
    output.hidden = false;
  }
  bedtime.addEventListener('input', update);
  update();
})();
