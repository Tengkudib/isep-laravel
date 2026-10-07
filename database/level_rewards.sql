ALTER TABLE shop_items ADD COLUMN unlock_level INT NULL DEFAULT NULL AFTER cost_xp;

INSERT INTO shop_items (name, description, item_type, cost_xp, unlock_level, border_style, extra_data, icon, sort_order) VALUES
('Pelajar Rajin',    'Ganjaran Level 2: gelaran untuk pelajar yang konsisten',        'title',       0, 2,  NULL, 'Pelajar Rajin', '📘', 10),
('Flame Bintang',    'Ganjaran Level 3: api streak bintang berkilau',                  'flame',       0, 3,  NULL, '💫', '💫', 10),
('Bingkai Zamrud',   'Ganjaran Level 5: bingkai hijau zamrud',                         'border',      0, 5,  'conic-gradient(from 0deg,#047857,#34D399,#A7F3D0,#10B981,#047857)', NULL, '💚', 10),
('Nama Api',         'Ganjaran Level 7: nama menyala seperti api',                     'name_effect', 0, 7,  NULL, 'name-fx-fire', '🔥', 10),
('Tema Galaksi',     'Ganjaran Level 10: sidebar ungu galaksi',                        'theme',       0, 10, NULL, '#1E1B4B,#7C3AED,#EC4899', '🌌', 10),
('Confetti Galaksi', 'Ganjaran Level 12: confetti warna galaksi',                      'celebration', 0, 12, NULL, '#7C3AED,#EC4899,#22D3EE,#F8FAFC,#A78BFA', '🌠', 10),
('Bingkai Aurora',   'Ganjaran Level 15: bingkai cahaya aurora',                       'border',      0, 15, 'conic-gradient(from 0deg,#22D3EE,#A78BFA,#F472B6,#34D399,#22D3EE)', NULL, '🌈', 11),
('Pakar Koding',     'Ganjaran Level 20: gelaran untuk pelajar berpengalaman',         'title',       0, 20, NULL, 'Pakar Koding', '🧠', 11),
('Nama Aurora',      'Ganjaran Level 25: nama bertukar warna seperti aurora',          'name_effect', 0, 25, NULL, 'name-fx-aurora', '✨', 11),
('Bingkai Diraja',   'Ganjaran Level 30: bingkai emas dan ungu diraja',                'border',      0, 30, 'conic-gradient(from 45deg,#FDE68A,#D4AF37,#7C3AED,#D4AF37,#FDE68A)', NULL, '👑', 12),
('Legenda iSEP',     'Ganjaran Level 40: gelaran tertinggi iSEP',                      'title',       0, 40, NULL, 'Legenda iSEP', '🏆', 12);
