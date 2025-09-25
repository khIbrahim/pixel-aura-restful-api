<?php

namespace Database\Factories\V1;

use App\Models\V1\Option;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Option>
 */
class OptionFactory extends Factory
{

    public function definition(): array
    {
        $options = [
            // Pains
            [
                'store_id' => 1,
                'name' => 'Pain Brioche',
                'description' => 'Pain légèrement sucré, texture moelleuse.',
                'price_cents' => 100,
                'is_active' => true,
            ],
            [
                'store_id' => 1,
                'name' => 'Pain Complet',
                'description' => 'Pain riche en fibres, goût plus rustique.',
                'price_cents' => 100,
                'is_active' => true,
            ],
            [
                'store_id' => 1,
                'name' => 'Pain Sans Gluten',
                'description' => 'Alternative pour une alimentation sans gluten.',
                'price_cents' => 150,
                'is_active' => true,
            ],
            [
                'store_id' => 1,
                'name' => 'Pain aux Céréales',
                'description' => 'Pain garni de graines variées, goût croquant.',
                'price_cents' => 150,
                'is_active' => true,
            ],
            [
                'store_id' => 1,
                'name' => 'Pain Rustique',
                'description' => 'Pain à la croûte dorée, saveur artisanale.',
                'price_cents' => 100,
                'is_active' => true,
            ],

            // Fromages
            [
                'store_id' => 1,
                'name' => 'Fromage Cheddar',
                'description' => 'Tranche de cheddar fondant.',
                'price_cents' => 100,
                'is_active' => true,
            ],
            [
                'store_id' => 1,
                'name' => 'Fromage Emmental',
                'description' => 'Fromage doux à trous, légèrement fruité.',
                'price_cents' => 120,
                'is_active' => true,
            ],
            [
                'store_id' => 1,
                'name' => 'Fromage Bleu',
                'description' => 'Fromage au goût corsé et crémeux.',
                'price_cents' => 150,
                'is_active' => true,
            ],
            [
                'store_id' => 1,
                'name' => 'Fromage Vegan',
                'description' => 'Alternative végétale au fromage classique.',
                'price_cents' => 150,
                'is_active' => true,
            ],
            [
                'store_id' => 1,
                'name' => 'Double Fromage',
                'description' => 'Portion supplémentaire de fromage au choix.',
                'price_cents' => 200,
                'is_active' => true,
            ],

            // Extras protéines
            [
                'store_id' => 1,
                'name' => 'Bacon Grillé',
                'description' => 'Tranches de bacon croustillant.',
                'price_cents' => 300,
                'is_active' => true,
            ],
            [
                'store_id' => 1,
                'name' => 'Steak Supplémentaire',
                'description' => 'Ajout d’un steak de bœuf supplémentaire.',
                'price_cents' => 400,
                'is_active' => true,
            ],
            [
                'store_id' => 1,
                'name' => 'Poulet Grillé',
                'description' => 'Filet de poulet tendre grillé.',
                'price_cents' => 350,
                'is_active' => true,
            ],
            [
                'store_id' => 1,
                'name' => 'Oeuf au Plat',
                'description' => 'Oeuf frais cuit à la plancha.',
                'price_cents' => 150,
                'is_active' => true,
            ],
            [
                'store_id' => 1,
                'name' => 'Galette Végétale',
                'description' => 'Alternative protéinée à base de légumes.',
                'price_cents' => 350,
                'is_active' => true,
            ],

            // Suppléments
            [
                'store_id' => 1,
                'name' => 'Avocat Frais',
                'description' => 'Tranches d’avocat mûr et crémeux.',
                'price_cents' => 400,
                'is_active' => true,
            ],
            [
                'store_id' => 1,
                'name' => 'Champignons Sautés',
                'description' => 'Champignons de Paris cuisinés à la poêle.',
                'price_cents' => 150,
                'is_active' => true,
            ],
            [
                'store_id' => 1,
                'name' => 'Oignons Caramélisés',
                'description' => 'Oignons doux caramélisés lentement.',
                'price_cents' => 150,
                'is_active' => true,
            ],
            [
                'store_id' => 1,
                'name' => 'Piments Jalapeños',
                'description' => 'Piments verts relevés pour plus de piquant.',
                'price_cents' => 100,
                'is_active' => true,
            ],
            [
                'store_id' => 1,
                'name' => 'Cornichons',
                'description' => 'Tranches de cornichons croquants.',
                'price_cents' => 50,
                'is_active' => true,
            ],

            // Sauces froides
            [
                'store_id' => 1,
                'name' => 'Ketchup',
                'description' => 'Sauce tomate sucrée et acidulée.',
                'price_cents' => 0,
                'is_active' => true,
            ],
            [
                'store_id' => 1,
                'name' => 'Mayonnaise',
                'description' => 'Sauce crémeuse et douce.',
                'price_cents' => 0,
                'is_active' => true,
            ],
            [
                'store_id' => 1,
                'name' => 'Sauce Barbecue',
                'description' => 'Sauce fumée et légèrement sucrée.',
                'price_cents' => 50,
                'is_active' => true,
            ],
            [
                'store_id' => 1,
                'name' => 'Sauce Moutarde',
                'description' => 'Condiment relevé à la moutarde.',
                'price_cents' => 0,
                'is_active' => true,
            ],
            [
                'store_id' => 1,
                'name' => 'Sauce Cocktail',
                'description' => 'Mélange doux à base de ketchup et mayonnaise.',
                'price_cents' => 50,
                'is_active' => true,
            ],

            // Sauces chaudes
            [
                'store_id' => 1,
                'name' => 'Sauce Fromage Chaud',
                'description' => 'Sauce crémeuse au fromage fondu.',
                'price_cents' => 100,
                'is_active' => true,
            ],
            [
                'store_id' => 1,
                'name' => 'Sauce Piquante',
                'description' => 'Sauce relevée pour amateurs de piment.',
                'price_cents' => 50,
                'is_active' => true,
            ],
            [
                'store_id' => 1,
                'name' => 'Sauce Curry Doux',
                'description' => 'Sauce chaude parfumée au curry.',
                'price_cents' => 100,
                'is_active' => true,
            ],

            // Accompagnements
            [
                'store_id' => 1,
                'name' => 'Frites Classiques',
                'description' => 'Pommes de terre frites croustillantes.',
                'price_cents' => 250,
                'is_active' => true,
            ],
            [
                'store_id' => 1,
                'name' => 'Frites Patate Douce',
                'description' => 'Frites de patate douce sucrées et fondantes.',
                'price_cents' => 300,
                'is_active' => true,
            ],
            [
                'store_id' => 1,
                'name' => 'Salade Verte',
                'description' => 'Mélange de jeunes pousses et vinaigrette.',
                'price_cents' => 200,
                'is_active' => true,
            ],
            [
                'store_id' => 1,
                'name' => 'Coleslaw',
                'description' => 'Salade de chou et carottes à la sauce crémeuse.',
                'price_cents' => 200,
                'is_active' => true,
            ],

            // Boissons
            [
                'store_id' => 1,
                'name' => 'Boisson Cola',
                'description' => 'Boisson gazeuse saveur cola.',
                'price_cents' => 250,
                'is_active' => true,
            ],
            [
                'store_id' => 1,
                'name' => 'Boisson Citron',
                'description' => 'Soda pétillant goût citron.',
                'price_cents' => 250,
                'is_active' => true,
            ],
            [
                'store_id' => 1,
                'name' => 'Eau Plate',
                'description' => 'Bouteille d’eau minérale.',
                'price_cents' => 200,
                'is_active' => true,
            ],
            [
                'store_id' => 1,
                'name' => 'Eau Pétillante',
                'description' => 'Eau gazeuse rafraîchissante.',
                'price_cents' => 200,
                'is_active' => true,
            ],
            [
                'store_id' => 1,
                'name' => 'Boisson Énergisante',
                'description' => 'Boisson gazeuse énergisante.',
                'price_cents' => 300,
                'is_active' => true,
            ],

            // Desserts
            [
                'store_id' => 1,
                'name' => 'Cookie Chocolat',
                'description' => 'Cookie moelleux aux pépites de chocolat.',
                'price_cents' => 250,
                'is_active' => true,
            ],
            [
                'store_id' => 1,
                'name' => 'Muffin Myrtilles',
                'description' => 'Muffin moelleux aux myrtilles.',
                'price_cents' => 300,
                'is_active' => true,
            ],
            [
                'store_id' => 1,
                'name' => 'Brownie',
                'description' => 'Brownie fondant au chocolat.',
                'price_cents' => 300,
                'is_active' => true,
            ],
            [
                'store_id' => 1,
                'name' => 'Yaourt Nature',
                'description' => 'Yaourt nature crémeux.',
                'price_cents' => 250,
                'is_active' => true,
            ],

            // Cuisson
            [
                'store_id' => 1,
                'name' => 'Cuisson Saignant',
                'description' => 'Steak cuit légèrement, cœur rouge.',
                'price_cents' => 0,
                'is_active' => true,
            ],
            [
                'store_id' => 1,
                'name' => 'Cuisson À Point',
                'description' => 'Steak cuit rosé à cœur.',
                'price_cents' => 0,
                'is_active' => true,
            ],
            [
                'store_id' => 1,
                'name' => 'Cuisson Bien Cuit',
                'description' => 'Steak cuit entièrement.',
                'price_cents' => 0,
                'is_active' => true,
            ],

            // Régimes / Tags
            [
                'store_id' => 1,
                'name' => 'Option Vegan',
                'description' => 'Convient à une alimentation végétalienne.',
                'price_cents' => 0,
                'is_active' => true,
            ],
            [
                'store_id' => 1,
                'name' => 'Option Végétarien',
                'description' => 'Convient à une alimentation végétarienne.',
                'price_cents' => 0,
                'is_active' => true,
            ],
            [
                'store_id' => 1,
                'name' => 'Sans Gluten',
                'description' => 'Convient à une alimentation sans gluten.',
                'price_cents' => 0,
                'is_active' => true,
            ],
            [
                'store_id' => 1,
                'name' => 'Sans Lactose',
                'description' => 'Convient à une alimentation sans lactose.',
                'price_cents' => 0,
                'is_active' => true,
            ],

            // Divers
            [
                'store_id' => 1,
                'name' => 'Glace Supplémentaire',
                'description' => 'Ajout de glaçons pour votre boisson.',
                'price_cents' => 0,
                'is_active' => true,
            ],
            [
                'store_id' => 1,
                'name' => 'Serviette Supplémentaire',
                'description' => 'Demande de serviette en plus.',
                'price_cents' => 0,
                'is_active' => true,
            ],
        ];

        return $options;
    }
}
