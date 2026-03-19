<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MenuController extends Controller
{
    /**
     * Get all menu data (categories and items)
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        // For now, return the same structure as assets/menu.json
        // Later, this can be moved to a database or external service
        $menuData = $this->getMenuData();
        
        return response()->json($menuData);
    }

    /**
     * Get a specific menu item by ID
     *
     * @param string $id
     * @return JsonResponse
     */
    public function show(string $id): JsonResponse
    {
        $menuData = $this->getMenuData();
        
        // Find the item by ID
        $item = collect($menuData['items'])->firstWhere('id', $id);
        
        if (!$item) {
            return response()->json([
                'message' => 'Item not found',
                'code' => 'ITEM_NOT_FOUND'
            ], 404);
        }
        
        return response()->json($item);
    }

    /**
     * Get menu data (same structure as assets/menu.json)
     *
     * @return array
     */
    private function getMenuData(): array
    {
        return [
            "categories" => [
                [
                    "id" => "cat-momo",
                    "name" => "Momo"
                ],
                [
                    "id" => "cat-drinks",
                    "name" => "Drinks"
                ]
            ],
            "items" => [
                [
                    "id" => "itm-classic-momo",
                    "name" => "Classic Chicken Momo",
                    "desc" => "Juicy chicken, house spice blend.",
                    "imageUrl" => "",
                    "basePrice" => [
                        "currency" => "NPR",
                        "amount" => 180
                    ],
                    "variants" => [
                        [
                            "id" => "v6",
                            "name" => "6 pcs",
                            "priceDiff" => [
                                "currency" => "NPR",
                                "amount" => 0
                            ]
                        ],
                        [
                            "id" => "v10",
                            "name" => "10 pcs",
                            "priceDiff" => [
                                "currency" => "NPR",
                                "amount" => 120
                            ]
                        ]
                    ],
                    "addOns" => [
                        [
                            "id" => "a-chili",
                            "name" => "Extra Chili Sauce",
                            "price" => [
                                "currency" => "NPR",
                                "amount" => 20
                            ]
                        ],
                        [
                            "id" => "a-soup",
                            "name" => "Hot Soup",
                            "price" => [
                                "currency" => "NPR",
                                "amount" => 30
                            ]
                        ]
                    ],
                    "categoryId" => "cat-momo",
                    "isAvailable" => true
                ],
                [
                    "id" => "itm-veg-momo",
                    "name" => "Vegetable Momo",
                    "desc" => "Fresh vegetables, aromatic spices.",
                    "imageUrl" => "",
                    "basePrice" => [
                        "currency" => "NPR",
                        "amount" => 160
                    ],
                    "variants" => [
                        [
                            "id" => "v6",
                            "name" => "6 pcs",
                            "priceDiff" => [
                                "currency" => "NPR",
                                "amount" => 0
                            ]
                        ],
                        [
                            "id" => "v10",
                            "name" => "10 pcs",
                            "priceDiff" => [
                                "currency" => "NPR",
                                "amount" => 100
                            ]
                        ]
                    ],
                    "addOns" => [
                        [
                            "id" => "a-chili",
                            "name" => "Extra Chili Sauce",
                            "price" => [
                                "currency" => "NPR",
                                "amount" => 20
                            ]
                        ]
                    ],
                    "categoryId" => "cat-momo",
                    "isAvailable" => true
                ],
                [
                    "id" => "itm-masala-tea",
                    "name" => "Masala Chai",
                    "desc" => "Traditional spiced tea with milk.",
                    "imageUrl" => "",
                    "basePrice" => [
                        "currency" => "NPR",
                        "amount" => 50
                    ],
                    "variants" => [],
                    "addOns" => [
                        [
                            "id" => "a-extra-milk",
                            "name" => "Extra Milk",
                            "price" => [
                                "currency" => "NPR",
                                "amount" => 10
                            ]
                        ]
                    ],
                    "categoryId" => "cat-drinks",
                    "isAvailable" => true
                ]
            ]
        ];
    }
}
