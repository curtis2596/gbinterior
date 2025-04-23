<?php

namespace App\Helpers;

use App\Acl\AccessControl;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class Menu
{
    private static $menus = [];
    private static $current_route_name = "";

    public static function setCurrentRouteName(String $current_route_name)
    {
        self::$current_route_name = strtolower(trim($current_route_name));
        BaseMenu::setCurrentRouteName(self::$current_route_name);
    }

    public static function get($auth_user_id)
    {
        self::$menus = [];

        // if ( Config::get('app.will_menu_cache') )
        // {
        //     $acccessControl = AccessControl::init();

        //     $cache_key = $acccessControl->getMenuCacheKey($auth_user_id);

        //     self::$menus = Cache::get($cache_key);

        //     if (!empty(self::$menus))
        //     {
        //         return self::$menus;
        //     }
        // }

        self::$menus[] = (new HomeMenu)->get();
        self::$menus[] = (new LeadMenu)->get();
        self::$menus[] = (new ComplaintMenu)->get();
        self::$menus[] = (new QuotationMenu)->get();
        self::$menus[] = (new SaleAndPurchaseMenu)->get();
        self::$menus[] = (new AccountingMenu)->get();
        self::$menus[] = (new MemberMenu)->get();
        self::$menus[] = (new EmployeeMenu)->get();
        self::$menus[] = (new Employee)->get();
        self::$menus[] = (new ReportMenu)->get();
        self::$menus[] = (new InventoryMenu)->get();
        self::$menus[] = (new ManufacturingMenu)->get();
        self::$menus[] = (new GeneralMenu)->get();
        self::$menus[] = (new TypeMenu)->get();
        self::$menus[] = (new SystemMenu)->get();
        self::$menus[] = (new LogMenu)->get();
        self::$menus[] = (new DeveloperMenu)->get();

        //d(self::$menus); exit;

        if (isset($cache_key)) {
            self::_filterMenuForUser();

            Cache::put($cache_key, self::$menus, laravel_constant("cache_time.menu"));
        }

        return self::checkForActive(self::$menus);;
    }

    public static function getBreadcums($menus)
    {
        $breadcums = self::findBreadCum($menus);

        //d($breadcums); exit;

        return $breadcums;
    }

    public static function checkForActive($menus)
    {
        foreach ($menus as $k => $menu) {
            if (isset($menu['route_name'])) {
                $menus[$k]['is_active'] = self::isActiveLink($menu);
            } else if (isset($menu['links'])) {
                $menus[$k]['links'] = self::checkForActive($menu['links']);
            }
        }

        return $menus;
    }

    public static function isActiveLink(array $link)
    {
        if ($link['route_name'] == self::$current_route_name) {
            return true;
        } else if (isset($link["related_links"]) && is_array($link["related_links"])) {
            foreach ($link["related_links"] as $related_link) {
                $is_active = self::isActiveLink($related_link);

                if ($is_active) {
                    return true;
                }
            }
        }

        return false;
    }


    private static function findBreadCum(array $menus, array $parents = [])
    {
        foreach ($menus as $menu) {
            $aray_helper = new ArrayHelper($menu);
            $parent = $aray_helper->getOnlyWhichHaveKeys(["title", "route_name"]);

            if (isset($menu['links'])) {
                $temp = $parents;
                $temp[] = $parent;
                $ret = self::findBreadCum($menu['links'], $temp);

                if ($ret) {
                    return $ret;
                }
            }

            if (isset($menu['related_links'])) {
                foreach ($menu['related_links'] as $related_link) {
                    if (isset($related_link['route_name'])) {
                        if ($related_link['route_name'] == self::$current_route_name) {
                            $parents[] = $parent;

                            $aray_helper = new ArrayHelper($related_link);
                            $temp = $aray_helper->getOnlyWhichHaveKeys(["title", "route_name"]);

                            $parents[] = $temp;

                            return $parents;
                        }
                    }
                }
            }

            if (isset($menu['route_name'])) {
                if ($menu['route_name'] == self::$current_route_name) {
                    $parents[] = $parent;
                    return $parents;
                }
            }
        }

        return [];
    }

    public static function getList(array $menus, String $prefix = "")
    {
        $list = [];
        foreach ($menus as $menu) {
            if (isset($menu['route_name'])) {
                $list[] = [
                    "title" => $prefix . $menu['title'],
                    "url" => route($menu['route_name'])
                ];
            } else if (isset($menu["links"])) {
                $list = array_merge($list, self::getList($menu["links"], $prefix . $menu['title'] . " -> "));
            }
        }

        return $list;
    }

    private static function _filterMenuForUser()
    {
        $role_id_list = [];
        foreach (Auth::user()->userRole->toArray() as $user_role) {
            $role_id_list[] = $user_role['role_id'];
        }

        $acccessControl = AccessControl::init();
        $allowed_route_name_list = $acccessControl->getListOfAllowedRouteNames($role_id_list);
        //d($allowed_route_name_list);

        foreach (self::$menus as $k => $sub_menu) {
            if (isset($sub_menu['links'])) {
                foreach ($sub_menu['links'] as $k2 => $sub_menu2) {
                    if (isset($sub_menu2['links'])) {
                        foreach ($sub_menu2['links'] as $k3 => $sub_menu3) {
                            if (isset($sub_menu3['route_name'])) {
                                if (!in_array($sub_menu3['route_name'], $allowed_route_name_list)) {
                                    unset($sub_menu2['links'][$k3]);
                                }
                            }
                        }

                        if (empty($sub_menu2['links'])) {
                            unset($sub_menu['links'][$k2]);
                        } else {
                            $sub_menu['links'][$k2] = $sub_menu2;
                        }
                    } else if (isset($sub_menu2['route_name'])) {
                        if (!in_array($sub_menu2['route_name'], $allowed_route_name_list)) {
                            unset($sub_menu['links'][$k2]);
                        }
                    }
                }
            } else if (isset($sub_menu['route_name'])) {
                if (!in_array($sub_menu['route_name'], $allowed_route_name_list)) {
                    unset(self::$menus[$k]);
                }
            }

            if (empty($sub_menu['links'])) {
                unset(self::$menus[$k]);
            } else {
                self::$menus[$k] = $sub_menu;
            }
        }
    }
}

class BaseMenu
{
    const ICON_MENU_ROOT = 'fas fa-layer-group';
    const ICON_MENU_ROOT_CHILD = 'fas fa-cube';
    const ICON_MENU_SUMMARY = 'fas fa-table';
    const ICON_MENU_CREATE = 'fas fa-plus-circle';
    const ICON_REPORT = 'fas fa-file-text';

    private static $current_route_name = "";

    const LINK_TYPE_SUMMARY = "summary";
    const LINK_TYPE_ADD = "add";
    const LINK_TYPE_EDIT = "edit";
    const LINK_TYPE_DELETE = "add";

    public static function setCurrentRouteName(String $current_route_name)
    {
        self::$current_route_name = strtolower(trim($current_route_name));
    }

    public static function get(): array
    {
        return [];
    }

    public static function getModule(String $title, $icon, array $links = [])
    {
        if (!$icon) {
            $icon = self::ICON_MENU_ROOT;
        }

        return [
            'title' => $title,
            'icon' => $icon,
            "links" => $links
        ];
    }


    public static function getLink(String $route_name, $title, String $icon, array $related_links = [], String $link_type = "")
    {
        if (!$title) {
            $title = self::getLinkTitleFromRouteName($route_name, $link_type);
        }

        $link = [
            "title" => $title,
            "icon" => "child-menu-icon " . $icon,
            "route_name" => trim($route_name),
            "related_links" => $related_links
        ];

        $link['is_active'] = self::isActiveLink($link);

        return $link;
    }

    public static function addRelatedLink(String $route_name, String $title, array $related_links = [])
    {
        $link = [
            "title" => $title,
            "route_name" => trim($route_name),
            "related_links" => $related_links
        ];

        $link['is_active'] = self::isActiveLink($link);

        return $link;
    }

    public static function isActiveLink(array $link)
    {
        if ($link['route_name'] == self::$current_route_name) {
            return true;
        } else if (isset($link["related_links"]) && is_array($link["related_links"])) {
            foreach ($link["related_links"] as $related_link) {
                $is_active = self::isActiveLink($related_link);

                if ($is_active) {
                    return true;
                }
            }
        }

        return false;
    }

    public static function getControllerDefaultLinks(String $routePrefix, String $title, String $icon = "")
    {
        $links = [
            "title" => $title,
            "icon" => $icon,
            "links" => [
                self::getLink($routePrefix . ".index", "Summary", self::ICON_MENU_SUMMARY, [
                    self::addRelatedLink($routePrefix . ".edit", "Edit"),
                    self::addRelatedLink($routePrefix . ".view", "View"),
                ]),
                self::getLink($routePrefix . ".create", "Create", self::ICON_MENU_CREATE),
            ],
        ];

        return $links;
    }

    public static function getLinkTitleFromRouteName(String $route_name, String $link_type)
    {
        $title = $route_name;

        $arr = explode(".", $route_name);

        if (count($arr) > 1) {
            $title = end($arr);
        }

        $title = str_replace("_", " ", $title);

        switch ($link_type) {
            case self::LINK_TYPE_SUMMARY:
                $title = str_replace("index", "summary", $title);
                break;
        }

        $title = ucwords($title);

        return $title;
    }
}

class HomeMenu extends BaseMenu
{
    public static function get(): array
    {
        $links = [];

        $links[] = self::getLink("dashboard", "Dashboard", 'fa-solid fa-gauge');

        return self::getModule("Home", 'fas fa-home', $links);
    }
}

class SystemMenu extends BaseMenu
{
    public static function get(): array
    {
        $links = [];

        $links[] = self::permission();
        $links[] = self::settings();
        $links[] = self::ledger_category();
        $links[] = self::auto_increaments();

        return self::getModule("System Manager", 'fas fa-cogs', $links);
    }

    public static function permission()
    {
        $routePrefix = "permissions";

        $links = [
            self::getLink($routePrefix . ".index", "Summary", self::ICON_MENU_SUMMARY),
            self::getLink($routePrefix . ".assign", "Assign", 'fa-solid fa-gear'),
            // self::getLink($routePrefix . ".assign_to_many", "Assign To Many", 'bx bx-grid-alt'),
        ];

        return self::getModule("Permissions", self::ICON_MENU_ROOT_CHILD, $links);
    }

    private static function settings()
    {
        $routePrefix = "settings";

        $links = [
            self::getLink($routePrefix . ".general", "General", self::ICON_MENU_CREATE),
        ];

        return self::getModule("Settings", self::ICON_MENU_ROOT_CHILD, $links);
    }
    private static function ledger_category()
    {
        $routePrefix = "ledger-category";

        $links = [
            "title" => "Ledger Category",
            "icon" => self::ICON_MENU_ROOT_CHILD,
            "links" => [
                self::getLink($routePrefix . ".index", "Summary", self::ICON_MENU_SUMMARY, [
                    self::addRelatedLink($routePrefix . ".edit", "Edit"),
                ]),
            ],
        ];

        return $links;
    }
    private static function auto_increaments()
    {
        $routePrefix = "auto-increaments";

        $links = self::getControllerDefaultLinks($routePrefix, "Auto Increament", self::ICON_MENU_ROOT_CHILD);

        return $links;
    }
}

class LogMenu extends BaseMenu
{
    public static function get(): array
    {
        $links = [];

        $routePrefix = "logs";

        $links = [
            // self::getLink($routePrefix . ".sql", "SQL", self::ICON_MENU_SUMMARY)
        ];

        return self::getModule("Logs", null, $links);
    }
}


class DeveloperMenu extends BaseMenu
{
    public static function get(): array
    {
        $links = [];

        $routePrefix = "developer";

        $links[] = self::getLink($routePrefix . ".sql_log", null, self::ICON_MENU_SUMMARY, [], self::LINK_TYPE_SUMMARY);
        $links[] = self::getLink($routePrefix . ".laravel_routes_index", null, self::ICON_MENU_SUMMARY, [], self::LINK_TYPE_SUMMARY);

        return self::getModule("Developer", null, $links);
    }
}
class LeadMenu extends BaseMenu
{
    public static function get(): array
    {
        $routePrefix = "leads";

        $links = self::getControllerDefaultLinks($routePrefix, "Lead", self::ICON_MENU_ROOT_CHILD);

        return $links;
    }
}

class ComplaintMenu extends BaseMenu
{
    public static function get(): array
    {
        $routePrefix = "new-complaint";

        $links = self::getControllerDefaultLinks($routePrefix, "Complaint", self::ICON_MENU_ROOT_CHILD);

        return $links;
    }
}
class QuotationMenu extends BaseMenu
{
    public static function get(): array
    {
        $routePrefix = "quotation";

        $links = self::getControllerDefaultLinks($routePrefix, "Quotation", self::ICON_MENU_ROOT_CHILD);

        return $links;
    }
}

class SaleAndPurchaseMenu extends BaseMenu
{
    public static function get(): array
    {
        $links = [];

        // $links[] = self::getLink("saleOrderIndex", "Sale Order", self::ICON_MENU_SUMMARY);

        // $links[] = self::getLink("purchaseOrderIndex", "Purchase Order", self::ICON_MENU_SUMMARY);
        // $links[] = self::salebill();
        // $links[] = self::purchasebill();
        // $links[] = self::directbill();
        $links[] = self::purchase_order();
        $links[] = self::purchase_bill();
        $links[] = self::sale_order();
        $links[] = self::sale_bill();

        // dd($links);
        return self::getModule("Sale And Purchase", null, $links);
    }

    // public static function purchase_order (): Array
    // {
    //     $routePrefix = "orders";

    //     $links = self::getControllerDefaultLinks($routePrefix, "Purchase Order", self::ICON_MENU_ROOT_CHILD);

    //     return $links;
    // }

    private static function salebill()
    {

        $links = [
            self::getLink("order.sale.bills", "Summary", self::ICON_MENU_SUMMARY),
            self::getLink("bill.sale.create", "Create", self::ICON_MENU_CREATE),
        ];

        return self::getModule("Sale Bills", self::ICON_MENU_ROOT_CHILD, $links);
    }

    private static function purchasebill()
    {

        $links = [
            self::getLink("order.purchase.bills", "Summary", self::ICON_MENU_SUMMARY),
            self::getLink("bill.purchase.create", "Create", self::ICON_MENU_CREATE),

        ];

        return self::getModule("Purchase Bills", self::ICON_MENU_ROOT_CHILD, $links);
    }
    private static function directbill()
    {

        $links = [
            self::getLink("direct.sale.listing", "Summary", self::ICON_MENU_SUMMARY),
            self::getLink("direct.sale.create", "Create", self::ICON_MENU_CREATE),

        ];

        return self::getModule("Direct Bills", self::ICON_MENU_ROOT_CHILD, $links);
    }

    public static function purchase_order(): array
    {
        $routePrefix = "purchase-orders";

        $links = self::getControllerDefaultLinks($routePrefix, "Purchase Order", self::ICON_MENU_ROOT_CHILD);

        return $links;
    }

    public static function purchase_bill(): array
    {
        $routePrefix = "purchase-bills";

        $links = self::getControllerDefaultLinks($routePrefix, "Purchase Bill", self::ICON_MENU_ROOT_CHILD);
        $links["links"]["Create With PO"] = self::getLink($routePrefix . ".create_with_po", "Create With PO", self::ICON_MENU_CREATE);

        $routePrefix = "purchase-bill-item-movement";
        $links["links"][0]['related_links'][] = self::getLink($routePrefix . ".index", "Item Movement", self::ICON_MENU_ROOT_CHILD);
        // d($links); exit;

        return $links;
    }


    public static function sale_order(): array
    {
        $routePrefix = "sale-orders";

        $links = self::getControllerDefaultLinks($routePrefix, "Sale Order", self::ICON_MENU_ROOT_CHILD);

        return $links;
    }

    public static function sale_bill(): array
    {
        $routePrefix = "sale-bills";

        $links = self::getControllerDefaultLinks($routePrefix, "Sale Bill", self::ICON_MENU_ROOT_CHILD);
        $links["links"][] = self::getLink($routePrefix . ".create_with_so", "Create With SO", self::ICON_MENU_CREATE);

        $routePrefix = "sale-bill-item-movement";
        $links["links"][0]['related_links'][] = self::getLink($routePrefix . ".index", "Item Movement", self::ICON_MENU_ROOT_CHILD);

        return $links;
    }
}

class AccountingMenu extends BaseMenu
{
    public static function get(): array
    {
        $links = [];

        $links[] = self::accounts();
        $links[] = self::payments();

        return self::getModule("Accounting", null, $links);
    }

    private static function accounts()
    {
        $routePrefix = "ledger-accounts";

        $links = self::getControllerDefaultLinks($routePrefix, "Ledger Account", self::ICON_MENU_ROOT_CHILD);

        return $links;
    }

    private static function payments()
    {
        $routePrefix = "ledger-payments";

        $links = self::getControllerDefaultLinks($routePrefix, "Payment", self::ICON_MENU_ROOT_CHILD);

        $links = [
            "title" => "Payment",
            "icon" => self::ICON_MENU_ROOT_CHILD,
            "links" => [
                self::getLink($routePrefix . ".index", "Summary", self::ICON_MENU_SUMMARY, [
                    self::addRelatedLink($routePrefix . ".edit", "Edit"),
                    self::addRelatedLink($routePrefix . ".show", "View"),
                    self::addRelatedLink($routePrefix . ".print", "Print"),
                ]),
                self::getLink($routePrefix . ".create", "Transfer", 'fas fa-money'),
            ],
        ];

        $links["links"][] = self::getLink($routePrefix . ".pay_for_purchase", NULL, 'fas fa-money');
        $links["links"][] = self::getLink($routePrefix . ".receive_for_sale", NULL, 'fas fa-money');
        $links["links"][] = self::getLink($routePrefix . ".pay_for_job_work", NULL, 'fas fa-money');

        return $links;
    }
}

class MemberMenu extends BaseMenu
{
    public static function get(): array
    {
        $links = [];

        $links[] = self::role();
        $links[] = self::user();

        return self::getModule("Member Manager", 'fas fa-users', $links);
    }

    private static function user()
    {
        $routePrefix = "user";

        $links = self::getControllerDefaultLinks($routePrefix, "Users", "fas fa-users");

        return $links;
    }

    private static function role()
    {
        $routePrefix = "role";

        $links = self::getControllerDefaultLinks($routePrefix, "Roles", self::ICON_MENU_ROOT_CHILD);

        return $links;
    }
}


class EmployeeMenu extends BaseMenu
{
    public static function get(): array
    {
        $links = [];

        $links[] = self::department();
        $links[] = self::designation();

        return self::getModule("Employee Manager", 'fas fa-users', $links);
    }

    private static function department()
    {
        $routePrefix = "department";

        $links = self::getControllerDefaultLinks($routePrefix, "Department", self::ICON_MENU_ROOT_CHILD);

        return $links;
    }
    private static function designation()
    {
        $routePrefix = "designation";

        $links = self::getControllerDefaultLinks($routePrefix, "Designation", self::ICON_MENU_ROOT_CHILD);

        return $links;
    }
}

class ReportMenu extends BaseMenu
{
    public static function get(): array
    {
        $links = [];

        // $links[] = self::stock();
        $links[] = self::ledger();
        $links[] = self::inventory();
        // $links[] = self::getLink("order-transports.index", "Order Transport", self::ICON_REPORT);
        // $links[] = self::getLink("transfer-transactions.index", "Transfer Transaction", self::ICON_REPORT);

        return self::getModule("Reports", null, $links);
    }

    private static function stock()
    {
        return [
            "title" => "Stock",
            "icon" => self::ICON_MENU_ROOT_CHILD,
            "links" => [
                self::getLink("ledger.stock.index", "Item Wise", self::ICON_REPORT),
                // self::getLink("narration.index", "Narration Wise", self::ICON_REPORT),
                self::getLink("itemLocationStock.index", "Location Wise", self::ICON_REPORT),
                self::getLink("location-stock.index", "Location Stock", self::ICON_REPORT),
                self::getLink("item-LocationStock.index", "Item Location Stock", self::ICON_REPORT),
                self::getLink("party-stock.index", "Party Stock", self::ICON_REPORT),
            ],
        ];
    }

    private static function ledger()
    {
        return [
            "title" => "Ledger",
            "icon" => self::ICON_MENU_ROOT_CHILD,
            "links" => [
                self::getLink("reports.ledger", "Transactions", self::ICON_REPORT),
            ],
        ];
    }
    private static function inventory()
    {
        return [
            "title" => "Inventory",
            "icon" => self::ICON_MENU_ROOT_CHILD,
            "links" => [
                self::getLink("reports.inventory", "Current Stock", self::ICON_REPORT),
            ],
        ];
    }
}


class InventoryMenu extends BaseMenu
{
    public static function get(): array
    {
        $links = [];

        $links[] = self::warehouse_movement();
        $links[] = self::party_movements();

        // dd($links);
        return self::getModule("Inventory", null, $links);
    }

    public static function warehouse_movement(): array
    {
        $routePrefix = "warehouse-movements";

        $links = self::getControllerDefaultLinks($routePrefix, "Warehouse Movement", self::ICON_MENU_ROOT_CHILD);

        return $links;
    }
    public static function party_movements(): array
    {
        $routePrefix = "party-movements";

        $links = self::getControllerDefaultLinks($routePrefix, "Party Movement", self::ICON_MENU_ROOT_CHILD);

        return $links;
    }
}

class ManufacturingMenu extends BaseMenu
{
    public static function get(): array
    {
        $links = [];

        $links[] = self::in_house_manufacturing();
        // $links[] = self::job_works_manufacturing();
        $links[] = self::job_order();
        $links[] = self::job_works_from_job_order();

        // dd($links);
        return self::getModule("Manufacturing", null, $links);
    }

    public static function in_house_manufacturing(): array
    {
        $routePrefix = "in-house-manufacturing";

        $links = self::getControllerDefaultLinks($routePrefix, "In House", self::ICON_MENU_ROOT_CHILD);

        return $links;
    }


    // public static function job_works_manufacturing(): array
    // {
    //     $routePrefix = "job-work-manufacturing";

    //     $links = self::getControllerDefaultLinks($routePrefix, "Job Work", self::ICON_MENU_ROOT_CHILD);

    //     return $links;
    // }

    public static function job_order(): array
    {
        $routePrefix = "job-orders";

        $links = self::getControllerDefaultLinks($routePrefix, "Job Order", self::ICON_MENU_ROOT_CHILD);

        return $links;
    }

    public static function job_works_from_job_order(): array
    {
        $routePrefix = "job-orders-receive";

        $links = self::getControllerDefaultLinks($routePrefix, "Job Order Receive", self::ICON_MENU_ROOT_CHILD);

        return $links;
    }
}

class GeneralMenu extends BaseMenu
{
    public static function get(): array
    {
        $links = [];

        $links[] = self::getLink("companies.index", "Company", 'fas fa-users');
        $links[] = self::state();
        $links[] = self::cities();
        $links[] = self::unit();
        $links[] = self::brands();
        $links[] = self::processes();

        // Items

        $links[] = self::item_groups();
        $links[] = self::item_categories();
        $links[] = self::items();

        $links[] = self::transports();
        $links[] = self::purposes();
        $links[] = self::sources();
        $links[] = self::party_catgeory();
        $links[] = self::parties();

        $links[] = self::warehouse();
        $links[] = self::complaintType();
        $links[] = self::staffType();
        $links[] = self::allowanceType();




        return self::getModule("General Menu", 'fas fa-users', $links);
    }

    private static function state()
    {
        $routePrefix = "state";

        $links = self::getControllerDefaultLinks($routePrefix, "State", self::ICON_MENU_ROOT_CHILD);

        return $links;
    }
    private static function cities()
    {
        $routePrefix = "cities";

        $links = self::getControllerDefaultLinks($routePrefix, "city", self::ICON_MENU_ROOT_CHILD);

        return $links;
    }
    private static function unit()
    {
        $routePrefix = "units";

        $links = self::getControllerDefaultLinks($routePrefix, "Unit", self::ICON_MENU_ROOT_CHILD);

        return $links;
    }
    private static function brands()
    {
        $routePrefix = "brands";

        $links = self::getControllerDefaultLinks($routePrefix, "Brand", self::ICON_MENU_ROOT_CHILD);

        return $links;
    }
    private static function processes()
    {
        $routePrefix = "processes";

        $links = self::getControllerDefaultLinks($routePrefix, "Process", self::ICON_MENU_ROOT_CHILD);

        return $links;
    }

    // Item Menu 

    private static function item_groups()
    {
        $routePrefix = "item-groups";

        $links = self::getControllerDefaultLinks($routePrefix, "Item Group", self::ICON_MENU_ROOT_CHILD);

        return $links;
    }
    private static function item_categories()
    {
        $routePrefix = "item-categories";

        $links = self::getControllerDefaultLinks($routePrefix, "Item Category", self::ICON_MENU_ROOT_CHILD);

        return $links;
    }
    private static function items()
    {
        $routePrefix = "items";

        $links = self::getControllerDefaultLinks($routePrefix, "Item", self::ICON_MENU_ROOT_CHILD);

        $links['links'][0]['related_links'][] = self::addRelatedLink($routePrefix . ".set_warehouse_opening_qty", "Set Warehouse Opening Qty");

        // d($links); exit;

        return $links;
    }

    private static function transports()
    {
        $routePrefix = "transports";

        $links = self::getControllerDefaultLinks($routePrefix, "Transport", self::ICON_MENU_ROOT_CHILD);

        return $links;
    }
    private static function purposes()
    {
        $routePrefix = "purposes";

        $links = self::getControllerDefaultLinks($routePrefix, "Purpose", self::ICON_MENU_ROOT_CHILD);

        return $links;
    }
    private static function sources()
    {
        $routePrefix = "sources";

        $links = self::getControllerDefaultLinks($routePrefix, "Source", self::ICON_MENU_ROOT_CHILD);

        return $links;
    }
    private static function party_catgeory()
    {
        $routePrefix = "categories";

        $links = self::getControllerDefaultLinks($routePrefix, "Party Category", self::ICON_MENU_ROOT_CHILD);

        return $links;
    }
    private static function parties()
    {
        $routePrefix = "party";

        $links = self::getControllerDefaultLinks($routePrefix, "Party", self::ICON_MENU_ROOT_CHILD);

        return $links;
    }

    private static function warehouse()
    {
        $routePrefix = "warehouses";

        $links = self::getControllerDefaultLinks($routePrefix, "Warehouse", self::ICON_MENU_ROOT_CHILD);

        return $links;
    }

    private static function complaintType()
    {
        $routePrefix = "complaint-type";

        $links = self::getControllerDefaultLinks($routePrefix, "Complaint Type", self::ICON_MENU_ROOT_CHILD);

        return $links;
    }
    private static function staffType()
    {
        $routePrefix = "staff-type";

        $links = self::getControllerDefaultLinks($routePrefix, "Staff Type", self::ICON_MENU_ROOT_CHILD);

        return $links;
    }
    private static function allowanceType()
    {
        $routePrefix = "allowance-type";

        $links = self::getControllerDefaultLinks($routePrefix, "Allowance Type", self::ICON_MENU_ROOT_CHILD);

        return $links;
    }
}

class TypeMenu extends BaseMenu
{
    public static function get(): array
    {
        $links = [];

        $links[] = self::type();

        return self::getModule("Type Menu", 'fas fa-users', $links);
    }

    private static function type()
    {
        $routePrefix = "type";

        $links = self::getControllerDefaultLinks($routePrefix, "Type", self::ICON_MENU_ROOT_CHILD);

        return $links;
    }
}

class Employee extends BaseMenu
{
    public static function get(): array
    {
        $links = [];

        $links[] = self::type();

        return self::getModule("Employee", 'fas fa-users', $links);
    }

    private static function type()
    {
        $routePrefix = "employee";

        $links = self::getControllerDefaultLinks($routePrefix, "Employee", self::ICON_MENU_ROOT_CHILD);

        return $links;
    }
}
