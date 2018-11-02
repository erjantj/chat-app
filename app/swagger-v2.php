Swagger settings:
<?php
/**
 * @SWG\Swagger(
 *     schemes={"http"},
 *     basePath="/api/v1",
 *     @SWG\Info(
 *         version="1.0.0",
 *         title="Chat app",
 *         description="Chat app",
 *         @SWG\Contact(
 *             email="yerzhan.torgayev@gmail.com"
 *         ),
 *     ),
 * )
 */
?>

Security schemes:
<?php
/**
 *  @SWG\SecurityScheme(
 *   securityDefinition="apiKey",
 *   type="apiKey",
 *   in="header",
 *   name="Authorization"
 * )
 */
?>


Responses:
<?php
/**
 * @SWG\Response(
 *   response="UnprocessableEntity",
 *   description="Unprocessable Entity"
 * ),
 * @SWG\Response(
 *   response="Forbidden",
 *   description="Forbidden"
 * ),
 * @SWG\Response(
 *   response="RecordNotFound",
 *   description="Record Not Found"
 * )
 */
?>


Defenitions:
User
<?php
/**
 * @SWG\Definition(
 *   definition="User",
 *   @SWG\Property(
 *      property="username",
 *      type="string",
 *      description="username",
 *      default=""
 *   ),
 * )
 */
?>

Message
<?php
/**
 * @SWG\Definition(
 *   definition="Message",
 *   @SWG\Property(
 *      property="sender_id",
 *      type="integer",
 *      description="Sender id",
 *      default=""
 *   ),
 *   @SWG\Property(
 *      property="recipient_id",
 *      type="integer",
 *      description="Recipient id",
 *      default=""
 *   ),
 *   @SWG\Property(
 *      property="message",
 *      type="string",
 *      description="Message",
 *      default=""
 *   ),
 * )
 */
?>