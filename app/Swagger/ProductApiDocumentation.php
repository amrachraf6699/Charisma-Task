<?php

namespace App\Swagger;

use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     title="Product Inventory Microservice API",
 *     version="1.0.0",
 *     description="REST API for managing products and stock levels."
 * )
 *
 * @OA\Server(
 *     url="http://localhost:8000/api",
 *     description="Local API server"
 * )
 *
 * @OA\Tag(
 *     name="Products",
 *     description="Product inventory operations"
 * )
 *
 * @OA\Schema(
 *     schema="Product",
 *     type="object",
 *     @OA\Property(property="id", type="string", format="uuid", example="a2406c76-3483-4344-8ca9-ebcd88caff0d"),
 *     @OA\Property(property="sku", type="string", example="product-12"),
 *     @OA\Property(property="name", type="string", example="Product 12"),
 *     @OA\Property(property="description", type="string", nullable=true, example="Product 12 Description"),
 *     @OA\Property(property="price", type="string", example="11.20"),
 *     @OA\Property(property="stock_quantity", type="integer", example=22),
 *     @OA\Property(property="low_stock_threshold", type="integer", example=12),
 *     @OA\Property(property="is_low_stock", type="boolean", example=false),
 *     @OA\Property(property="status", type="string", enum={"active", "inactive", "discontinued"}, example="active"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="created_at_humanly", type="string", example="3 hours ago"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at_humanly", type="string", example="1 second ago")
 * )
 *
 * @OA\Schema(
 *     schema="ProductPayload",
 *     required={"sku", "name", "price", "stock_quantity", "low_stock_threshold", "status"},
 *     @OA\Property(property="sku", type="string", maxLength=255, example="product-12"),
 *     @OA\Property(property="name", type="string", minLength=3, maxLength=255, example="Product 12"),
 *     @OA\Property(property="description", type="string", nullable=true, example="Product 12 Description"),
 *     @OA\Property(property="price", type="number", minimum=0, example=11.2),
 *     @OA\Property(property="stock_quantity", type="integer", minimum=0, example=22),
 *     @OA\Property(property="low_stock_threshold", type="integer", minimum=0, example=12),
 *     @OA\Property(property="status", type="string", enum={"active", "inactive", "discontinued"}, example="active")
 * )
 *
 * @OA\Schema(
 *     schema="AdjustStockPayload",
 *     required={"quantity"},
 *     @OA\Property(
 *         property="quantity",
 *         type="integer",
 *         example=-3,
 *         description="Positive values increment stock; negative values decrement stock. Zero is invalid."
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="Pagination",
 *     @OA\Property(property="current_page", type="integer", example=1),
 *     @OA\Property(property="last_page", type="integer", example=3),
 *     @OA\Property(property="per_page", type="integer", example=15),
 *     @OA\Property(property="total", type="integer", example=32)
 * )
 *
 * @OA\Schema(
 *     schema="ProductResponse",
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="message", type="string", example="Product updated successfully."),
 *     @OA\Property(property="data", ref="#/components/schemas/Product")
 * )
 *
 * @OA\Schema(
 *     schema="ProductCollectionResponse",
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="message", type="string", example="Products fetched successfully."),
 *     @OA\Property(
 *         property="data",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/Product")
 *     ),
 *     @OA\Property(
 *         property="meta",
 *         type="object",
 *         @OA\Property(property="pagination", ref="#/components/schemas/Pagination")
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="MessageResponse",
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="message", type="string", example="Product deleted successfully."),
 *     @OA\Property(property="data", nullable=true, example=null)
 * )
 *
 * @OA\Schema(
 *     schema="ErrorResponse",
 *     @OA\Property(property="success", type="boolean", example=false),
 *     @OA\Property(property="message", type="string", example="Product was not found."),
 *     @OA\Property(property="data", nullable=true, example=null)
 * )
 *
 * @OA\Schema(
 *     schema="ValidationErrorResponse",
 *     @OA\Property(property="success", type="boolean", example=false),
 *     @OA\Property(property="message", type="string", example="Validation failed."),
 *     @OA\Property(
 *         property="data",
 *         type="object",
 *         @OA\AdditionalProperties(
 *             type="array",
 *             @OA\Items(type="string")
 *         )
 *     )
 * )
 */
class ProductApiDocumentation
{
    /**
     * @OA\Get(
     *     path="/products",
     *     tags={"Products"},
     *     summary="List products",
     *     description="Returns a paginated product list. This endpoint is cached in Redis.",
     *     @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer", minimum=1, example=1)),
     *     @OA\Response(response=200, description="Products fetched successfully.", @OA\JsonContent(ref="#/components/schemas/ProductCollectionResponse")),
     *     @OA\Response(response=429, description="Too many requests.", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function index(): void
    {
    }

    /**
     * @OA\Post(
     *     path="/products",
     *     tags={"Products"},
     *     summary="Create product",
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/ProductPayload")),
     *     @OA\Response(response=200, description="Product created successfully.", @OA\JsonContent(ref="#/components/schemas/ProductResponse")),
     *     @OA\Response(response=422, description="Validation failed.", @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")),
     *     @OA\Response(response=429, description="Too many requests.", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function store(): void
    {
    }

    /**
     * @OA\Get(
     *     path="/products/{product}",
     *     tags={"Products"},
     *     summary="Get product",
     *     @OA\Parameter(name="product", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Product fetched successfully.", @OA\JsonContent(ref="#/components/schemas/ProductResponse")),
     *     @OA\Response(response=404, description="Product not found.", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=429, description="Too many requests.", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function show(): void
    {
    }

    /**
     * @OA\Put(
     *     path="/products/{product}",
     *     tags={"Products"},
     *     summary="Update product",
     *     @OA\Parameter(name="product", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/ProductPayload")),
     *     @OA\Response(response=200, description="Product updated successfully.", @OA\JsonContent(ref="#/components/schemas/ProductResponse")),
     *     @OA\Response(response=404, description="Product not found.", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=422, description="Validation failed.", @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")),
     *     @OA\Response(response=429, description="Too many requests.", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function update(): void
    {
    }

    /**
     * @OA\Delete(
     *     path="/products/{product}",
     *     tags={"Products"},
     *     summary="Soft delete product",
     *     @OA\Parameter(name="product", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Product deleted successfully.", @OA\JsonContent(ref="#/components/schemas/MessageResponse")),
     *     @OA\Response(response=404, description="Product not found.", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=429, description="Too many requests.", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function destroy(): void
    {
    }

    /**
     * @OA\Post(
     *     path="/products/{product}/stock",
     *     tags={"Products"},
     *     summary="Adjust product stock",
     *     description="Use a positive quantity to increment stock and a negative quantity to decrement stock.",
     *     @OA\Parameter(name="product", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/AdjustStockPayload")),
     *     @OA\Response(response=200, description="Product stock adjusted successfully.", @OA\JsonContent(ref="#/components/schemas/ProductResponse")),
     *     @OA\Response(response=404, description="Product not found.", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=422, description="Validation failed.", @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")),
     *     @OA\Response(response=429, description="Too many requests.", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function adjustStock(): void
    {
    }

    /**
     * @OA\Get(
     *     path="/products/low-stock",
     *     tags={"Products"},
     *     summary="List low-stock products",
     *     @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer", minimum=1, example=1)),
     *     @OA\Response(response=200, description="Low stock products fetched successfully.", @OA\JsonContent(ref="#/components/schemas/ProductCollectionResponse")),
     *     @OA\Response(response=429, description="Too many requests.", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function lowStock(): void
    {
    }
}
