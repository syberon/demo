/*
 * Copyright (c) 2019.
 *
 * @author Syber
 */

import {Fancybox} from "@fancyapps/ui";
import ru from "@fancyapps/ui/src/Fancybox/l10n/ru"
import "@fancyapps/ui/dist/fancybox.css";

window.addEventListener('load', function() {
    // Инициализация fancybox
    Fancybox.bind("a.lightbox, [data-fancybox]", {
        l10n: ru
    });
});